<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use App\Http\Requests\GenerateHonorariumRequest;
use App\Models\AcademicYear;
use App\Models\Teacher;
use App\Models\TeacherHonorarium;
use App\Services\AcademicYearService;
use App\Services\TeacherHonorariumService;
use App\Services\TeachingHourService;
use Barryvdh\DomPDF\Facade\Pdf;
use Inertia\Inertia;
use Inertia\Response;

class HonorariumController extends Controller
{
    public function __construct(
        private TeacherHonorariumService $service,
        private AcademicYearService $academicYearService,
        private TeachingHourService $teachingHourService,
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

        $this->service->generate($teacher, $academicYear, $validated['period_month'], $validated['period_year']);

        return redirect()->back()->with('success', 'Slip honor berhasil dibuat.');
    }

    public function markPaid(TeacherHonorarium $honorarium)
    {
        if ($honorarium->isPaid()) {
            return redirect()->back()->withErrors(['status' => 'Slip honor ini sudah berstatus lunas.']);
        }

        $this->service->markPaid($honorarium, auth()->user());

        return redirect()->back()->with('success', 'Honor berhasil ditandai sebagai lunas.');
    }

    public function destroy(TeacherHonorarium $honorarium)
    {
        if ($honorarium->isPaid()) {
            return redirect()->back()->withErrors(['status' => 'Slip honor yang sudah lunas tidak bisa dihapus.']);
        }

        $this->service->delete($honorarium);

        return redirect()->back()->with('success', 'Slip honor berhasil dihapus.');
    }

    public function downloadSlip(TeacherHonorarium $honorarium)
    {
        $honorarium->load(['teacher.user', 'academicYear', 'tuKeuangan']);

        $schoolSetting = \App\Models\SchoolSetting::first();

        $logoBase64 = null;
        $logoMime   = null;
        if ($schoolSetting?->logo) {
            $path = storage_path('app/public/' . $schoolSetting->logo);
            if (file_exists($path)) {
                $logoBase64 = base64_encode(file_get_contents($path));
                $logoMime   = mime_content_type($path);
            }
        }

        $pdf = Pdf::loadView('pdf.honorarium_slip', [
            'honorarium'    => $honorarium,
            'school'        => $schoolSetting,
            'logo_base64'   => $logoBase64,
            'logo_mime'     => $logoMime,
            'period_label'  => $honorarium->periodLabel(),
        ])->setPaper('a5', 'portrait');

        $filename = 'slip-honor-' . str($honorarium->teacher->user->name)->slug() . '-' . $honorarium->period_month . '-' . $honorarium->period_year . '.pdf';

        return $pdf->download($filename);
    }
}
