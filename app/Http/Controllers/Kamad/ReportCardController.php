<?php

namespace App\Http\Controllers\Kamad;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\ReportCard;
use App\Services\AcademicYearService;
use App\Services\ClassroomService;
use App\Services\NotificationService;
use App\Services\ReportCardService;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ReportCardController extends Controller
{
    public function __construct(
        private ReportCardService $service,
        private AcademicYearService $academicYearService,
        private ClassroomService $classroomService,
        private NotificationService $notif,
    ) {}

    public function index(): Response
    {
        $activeYear = $this->academicYearService->getActive();

        // Hitung jumlah raport per kelas, per semester, per status
        // Struktur: { classroomId: { semester: { status: count } } }
        $statsRaw = $activeYear
            ? ReportCard::where('academic_year_id', $activeYear->id)
                ->select('classroom_id', 'semester', 'status', DB::raw('count(*) as count'))
                ->groupBy('classroom_id', 'semester', 'status')
                ->get()
            : collect();

        $stats = [];
        foreach ($statsRaw as $row) {
            $stats[$row->classroom_id][$row->semester][$row->status] = $row->count;
        }

        return Inertia::render('Kamad/ReportCard/Index', [
            'classrooms'      => $this->classroomService->getAll(),
            'activeYear'      => $activeYear,
            'reportCardStats' => $stats,
        ]);
    }

    public function generate(Classroom $classroom)
    {
        $activeYear = $this->academicYearService->getActive();

        if (!$activeYear) {
            return redirect()->back()->with('error', 'Tidak ada tahun ajaran aktif.');
        }

        $this->service->generateForClass($classroom, $activeYear, request('semester', 1));

        return redirect()->back()->with('success', 'Raport berhasil digenerate.');
    }

    public function approve(ReportCard $reportCard)
    {
        if ($reportCard->isApproved()) {
            return redirect()->back()->with('error', 'Raport sudah disetujui.');
        }

        if (!$reportCard->isWaitingApproval()) {
            return redirect()->back()->with('error', 'Raport belum diajukan untuk persetujuan.');
        }

        $this->service->approve($reportCard, request()->user());

        // Notifikasi ke siswa bahwa raportnya sudah disetujui
        $reportCard->load('student.user', 'classroom');
        if ($reportCard->student?->user) {
            $semester = 'Semester ' . $reportCard->semester;
            $kelas    = $reportCard->classroom?->name ?? '';
            $this->notif->send(
                $reportCard->student->user,
                'report_card_approved',
                'Raport Disetujui',
                "Raport {$semester} kelas {$kelas} sudah disetujui. Silakan download raport Anda.",
                ['report_card_id' => $reportCard->id]
            );
        }

        return redirect()->back()->with('success', 'Raport berhasil disetujui.');
    }

    public function approveAll(Classroom $classroom)
    {
        $activeYear = $this->academicYearService->getActive();

        if (!$activeYear) {
            return redirect()->back()->with('error', 'Tidak ada tahun ajaran aktif.');
        }

        $semester = request('semester', 1);

        // Ambil raport yang akan di-approve untuk notifikasi
        $pending = ReportCard::where('classroom_id', $classroom->id)
            ->where('academic_year_id', $activeYear->id)
            ->where('semester', $semester)
            ->where('status', 'waiting_approval')
            ->with('student.user')
            ->get();

        $this->service->approveAll($classroom, $activeYear, $semester, request()->user());

        // Notifikasi ke setiap siswa
        $kelas = $classroom->name;
        foreach ($pending as $rc) {
            if ($rc->student?->user) {
                $this->notif->send(
                    $rc->student->user,
                    'report_card_approved',
                    'Raport Disetujui',
                    "Raport Semester {$semester} kelas {$kelas} sudah disetujui. Silakan download raport Anda.",
                    ['report_card_id' => $rc->id]
                );
            }
        }

        return redirect()->back()->with('success', 'Semua raport berhasil disetujui.');
    }

    public function verify(string $verifyCode): Response
    {
        $reportCard = $this->service->verifyCode($verifyCode);

        return Inertia::render('Public/ReportCardVerify', [
            'reportCard' => $reportCard ? [
                'student_name'   => $reportCard->student->name,
                'student_nis'    => $reportCard->student->nis,
                'classroom_name' => $reportCard->classroom->name,
                'academic_year'  => $reportCard->classroom->academicYear->name,
                'semester'       => $reportCard->semester,
                'approved_at'    => $reportCard->approved_at?->translatedFormat('d F Y'),
            ] : null,
        ]);
    }
}
