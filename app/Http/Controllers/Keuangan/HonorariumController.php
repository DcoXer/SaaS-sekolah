<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use App\Http\Requests\GenerateHonorariumRequest;
use App\Models\AcademicYear;
use App\Models\Teacher;
use App\Models\TeacherHonorarium;
use App\Jobs\SendHonorariumSlipJob;
use App\Services\AcademicYearService;
use App\Services\TeacherAttendanceService;
use App\Services\TeacherHonorariumService;
use App\Services\TeachingHourService;
use App\Services\WhatsAppService;
use App\Helpers\QrCodeHelper;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class HonorariumController extends Controller
{
    public function __construct(
        private TeacherHonorariumService $service,
        private AcademicYearService $academicYearService,
        private TeacherAttendanceService $attendanceService,
        private TeachingHourService $teachingHourService,
        private WhatsAppService $whatsAppService,
    ) {}

    public function index(): Response
    {
        $academicYears = $this->academicYearService->getAll();
        $activeYear    = $this->academicYearService->getActive();
        $teachers      = Teacher::with('user')->get();

        $filters = [
            'month'      => request('month'),
            'year'       => request('year'),
            'status'     => request('status'),
            'teacher_id' => request('teacher_id'),
        ];

        $honorariums = $this->service->getAll($filters);

        // Data untuk form generate: guru yang punya jam pelajaran di tahun ajaran aktif
        $teachersWithHours = $activeYear
            ? Teacher::with('user')
                ->whereHas('teachingHours', fn($q) => $q->where('academic_year_id', $activeYear->id))
                ->get()
            : collect();

        return Inertia::render('Keuangan/Honorarium/Index', [
            'honorariums'      => $honorariums,
            'academicYears'    => $academicYears,
            'activeYear'       => $activeYear,
            'teachers'         => $teachers,
            'teachersWithHours' => $teachersWithHours,
            'filters'          => $filters,
        ]);
    }

    public function generate(GenerateHonorariumRequest $request)
    {
        $validated   = $request->validated();
        $teacher     = Teacher::findOrFail($validated['teacher_id']);
        $academicYear = AcademicYear::findOrFail($validated['academic_year_id']);

        if ($this->service->alreadyGenerated($teacher, $validated['period_month'], $validated['period_year'])) {
            return redirect()->back()->withErrors(['period_month' => 'Slip honor untuk periode ini sudah pernah dibuat.']);
        }

        // Cek apakah guru punya konfigurasi jam pelajaran
        $teachingHour = $this->teachingHourService->getByTeacherAndYear($teacher, $academicYear);
        if (!$teachingHour) {
            return redirect()->back()->withErrors(['teacher_id' => 'Guru ini belum memiliki konfigurasi jam pelajaran untuk tahun ajaran tersebut.']);
        }

        // Cek kelengkapan absensi bulan tersebut
        if (!$this->attendanceService->isMonthComplete($teacher, $validated['period_month'], $validated['period_year'])) {
            return redirect()->back()->withErrors(['period_month' => 'Absensi guru di bulan ini belum lengkap. Pastikan semua hari kerja sudah diisi sebelum membuat slip.']);
        }

        $this->service->generate($teacher, $academicYear, $validated['period_month'], $validated['period_year']);

        return redirect()->back()->with('success', 'Slip honor berhasil dibuat.');
    }

    public function generateAll(\Illuminate\Http\Request $request)
    {
        $validated = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'period_month'     => 'required|integer|between:1,12',
            'period_year'      => 'required|integer|min:2000',
        ]);

        $academicYear = AcademicYear::findOrFail($validated['academic_year_id']);
        $result = $this->service->generateAll($academicYear, (int) $validated['period_month'], (int) $validated['period_year']);

        if ($result['created'] === 0 && $result['skipped'] === 0 && $result['incomplete'] === 0) {
            return redirect()->back()->withErrors(['academic_year_id' => 'Tidak ada guru dengan konfigurasi jam pelajaran untuk tahun ajaran ini.']);
        }

        if ($result['created'] === 0 && $result['incomplete'] > 0) {
            return redirect()->back()->withErrors(['period_month' => "Tidak ada slip yang dibuat. {$result['incomplete']} guru absensinya belum lengkap di periode ini."]);
        }

        $msg = "Berhasil membuat {$result['created']} slip honor.";
        if ($result['skipped'] > 0) {
            $msg .= " {$result['skipped']} guru dilewati (sudah punya slip).";
        }
        if ($result['incomplete'] > 0) {
            $msg .= " {$result['incomplete']} guru dilewati (absensi belum lengkap).";
        }

        return redirect()->back()->with('success', $msg);
    }

    public function markPaid(TeacherHonorarium $honorarium)
    {
        if ($honorarium->isPaid()) {
            return redirect()->back()->withErrors(['status' => 'Slip honor ini sudah berstatus lunas.']);
        }

        $this->service->markPaid($honorarium, auth()->user());

        return redirect()->back()->with('success', 'Honor berhasil ditandai sebagai lunas.');
    }

    public function markAllPaid(\Illuminate\Http\Request $request)
    {
        $validated = $request->validate([
            'period_month' => 'required|integer|between:1,12',
            'period_year'  => 'required|integer|min:2000',
        ]);

        $count = $this->service->markAllPaid(
            (int) $validated['period_month'],
            (int) $validated['period_year'],
            auth()->user(),
        );

        if ($count === 0) {
            return redirect()->back()->withErrors(['period_month' => 'Tidak ada slip honor berstatus belum dibayar untuk periode ini.']);
        }

        return redirect()->back()->with('success', "Berhasil menandai {$count} slip honor sebagai lunas.");
    }

    public function destroy(TeacherHonorarium $honorarium)
    {
        if ($honorarium->isPaid()) {
            return redirect()->back()->withErrors(['status' => 'Slip honor yang sudah lunas tidak bisa dihapus.']);
        }

        $this->service->delete($honorarium);

        return redirect()->back()->with('success', 'Slip honor berhasil dihapus.');
    }

    private function loadPdfAssets(?\App\Models\SchoolSetting $school, ?\App\Models\User $tuKeuangan): array
    {
        $logoBase64  = $logoMime  = null;
        $stampBase64 = $stampMime = null;
        $sigBase64   = $sigMime   = null;

        $loadImage = function (?string $relativePath): array {
            if (!$relativePath) return [null, null];
            $abs = storage_path('app/public/' . $relativePath);
            if (!file_exists($abs)) return [null, null];
            return [base64_encode(file_get_contents($abs)), mime_content_type($abs) ?: 'image/png'];
        };

        [$logoBase64,  $logoMime]  = $loadImage($school?->logo);
        [$stampBase64, $stampMime] = $loadImage($school?->stamp);
        [$sigBase64,   $sigMime]   = $loadImage($tuKeuangan?->signature);

        return [$logoBase64, $logoMime, $stampBase64, $stampMime, $sigBase64, $sigMime];
    }

    public function sendAllSlips(\Illuminate\Http\Request $request)
    {
        $validated = $request->validate([
            'period_month' => 'required|integer|between:1,12',
            'period_year'  => 'required|integer|min:2000',
        ]);

        $slips = TeacherHonorarium::with(['teacher'])
            ->where('period_month', $validated['period_month'])
            ->where('period_year', $validated['period_year'])
            ->get();

        if ($slips->isEmpty()) {
            return redirect()->back()->withErrors(['period_month' => 'Tidak ada slip honor untuk periode ini.']);
        }

        $queued  = 0;
        $skipped = 0;
        $delay   = 0; // detik, mulai dari 0

        foreach ($slips as $honorarium) {
            if (empty($honorarium->teacher->phone)) {
                $skipped++;
                continue;
            }

            SendHonorariumSlipJob::dispatch($honorarium->id)
                ->delay(now()->addSeconds($delay));

            $queued++;
            $delay += 60; // jeda 1 menit per guru
        }

        if ($queued === 0) {
            return redirect()->back()->withErrors(['period_month' => 'Tidak ada guru dengan nomor HP untuk periode ini.']);
        }

        $estimasi = $queued > 1 ? ' (estimasi selesai ~' . $queued . ' menit)' : '';
        $msg      = "Pengiriman slip ke {$queued} guru sudah dijadwalkan{$estimasi}.";
        if ($skipped > 0) $msg .= " {$skipped} guru dilewati (no. HP kosong).";

        return redirect()->back()->with('success', $msg);
    }

    public function sendSlip(TeacherHonorarium $honorarium)
    {
        $honorarium->load(['teacher.user', 'academicYear', 'tuKeuangan']);

        $phone = $honorarium->teacher->phone;
        if (empty($phone)) {
            return redirect()->back()->withErrors(['whatsapp' => 'Nomor HP guru tidak ditemukan. Lengkapi data guru terlebih dahulu.']);
        }

        $schoolSetting = \App\Models\SchoolSetting::first();
        [$logoBase64, $logoMime, $stampBase64, $stampMime, $sigBase64, $sigMime] = $this->loadPdfAssets($schoolSetting, $honorarium->tuKeuangan);

        $verifyUrl = route('honor.verify', $honorarium->slip_code);
        $qrPng     = QrCodeHelper::pngBase64($verifyUrl);

        $pdf = Pdf::loadView('pdf.honorarium_slip', [
            'honorarium'   => $honorarium,
            'school'       => $schoolSetting,
            'logo_base64'  => $logoBase64,
            'logo_mime'    => $logoMime,
            'stamp_base64' => $stampBase64,
            'stamp_mime'   => $stampMime,
            'sig_base64'   => $sigBase64,
            'sig_mime'     => $sigMime,
            'period_label' => $honorarium->periodLabel(),
            'qr_png'       => $qrPng,
            'verify_url'   => $verifyUrl,
        ])->setPaper('a5', 'portrait');

        // Simpan PDF sementara di local disk (tidak dapat diakses publik secara langsung)
        $uuid         = Str::uuid()->toString();
        $tempFilename = 'temp/' . $uuid . '.pdf';
        Storage::disk('local')->put($tempFilename, $pdf->output());

        // Buat signed URL yang expire 10 menit — hanya bisa diakses via signature
        $fileUrl  = URL::temporarySignedRoute('honorariums.temp-slip', now()->addMinutes(10), ['uuid' => $uuid]);
        $filename = 'slip-honor-' . str($honorarium->teacher->user->name)->slug() . '-' . $honorarium->period_month . '-' . $honorarium->period_year . '.pdf';

        $fmt     = fn($n) => 'Rp ' . number_format($n, 0, ',', '.');
        $message = "Assalamu'alaikum, {$honorarium->teacher->user->name}.\n\n"
            . "Berikut slip honor Anda periode *{$honorarium->periodLabel()}*.\n\n"
            . "• Jam Pelajaran : {$fmt($honorarium->teaching_hours_amount)} ({$honorarium->teaching_hours} jam)\n"
            . "• Transport     : {$fmt($honorarium->transport_amount)} ({$honorarium->transport_days} hari)\n"
            . "• *Total Honor  : {$fmt($honorarium->total_amount)}*\n\n"
            . "Terima kasih 🙏";

        $result = $this->whatsAppService->sendDocument($phone, $message, $fileUrl, $filename);

        // Hapus file temp setelah dikirim
        Storage::disk('local')->delete($tempFilename);

        if (!$result['success']) {
            return redirect()->back()->withErrors(['whatsapp' => 'Gagal kirim WA: ' . $result['message']]);
        }

        return redirect()->back()->with('success', "Slip honor {$honorarium->teacher->user->name} berhasil dikirim ke WhatsApp.");
    }

    public function downloadSlip(TeacherHonorarium $honorarium)
    {
        $honorarium->load(['teacher.user', 'academicYear', 'tuKeuangan']);

        $schoolSetting = \App\Models\SchoolSetting::first();
        [$logoBase64, $logoMime, $stampBase64, $stampMime, $sigBase64, $sigMime] = $this->loadPdfAssets($schoolSetting, $honorarium->tuKeuangan);

        $verifyUrl = route('honor.verify', $honorarium->slip_code);
        $qrPng     = QrCodeHelper::pngBase64($verifyUrl);

        $pdf = Pdf::loadView('pdf.honorarium_slip', [
            'honorarium'    => $honorarium,
            'school'        => $schoolSetting,
            'logo_base64'   => $logoBase64,
            'logo_mime'     => $logoMime,
            'stamp_base64'  => $stampBase64,
            'stamp_mime'    => $stampMime,
            'sig_base64'    => $sigBase64,
            'sig_mime'      => $sigMime,
            'period_label'  => $honorarium->periodLabel(),
            'qr_png'        => $qrPng,
            'verify_url'    => $verifyUrl,
        ])->setPaper('a5', 'portrait');

        $filename = 'slip-honor-' . str($honorarium->teacher->user->name)->slug() . '-' . $honorarium->period_month . '-' . $honorarium->period_year . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Serve temp slip PDF via signed URL (digunakan oleh WhatsApp/Fonnte saat download).
     * Route ini public tapi wajib punya signature yang valid.
     */
    public function serveTempSlip(string $uuid): SymfonyResponse
    {
        // Validasi format UUID untuk mencegah path traversal
        if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $uuid)) {
            abort(404);
        }

        $path = 'temp/' . $uuid . '.pdf';

        if (!Storage::disk('local')->exists($path)) {
            abort(404);
        }

        return Storage::disk('local')->response($path, null, ['Content-Type' => 'application/pdf']);
    }
}
