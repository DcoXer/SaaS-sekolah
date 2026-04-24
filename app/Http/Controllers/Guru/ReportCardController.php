<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateReportCardNotesRequest;
use App\Models\ReportCard;
use App\Models\SchoolSetting;
use App\Services\AcademicYearService;
use App\Services\ClassroomService;
use App\Services\ReportCardService;
use Barryvdh\DomPDF\Facade\Pdf;
use Inertia\Inertia;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Inertia\Response;

class ReportCardController extends Controller
{
    public function __construct(
        private ReportCardService $service,
        private AcademicYearService $academicYearService,
        private ClassroomService $classroomService,
    ) {}

    public function index(): Response
    {
        $user       = request()->user();
        $teacher    = $user->teacher;
        $activeYear = $this->academicYearService->getActive();

        $classroom = $teacher?->homeroomClassrooms()
                              ->where('academic_year_id', $activeYear?->id)
                              ->first();

        $semester = (int) request('semester', 1);

        return Inertia::render('Guru/ReportCard/Index', [
            'reportCards' => $classroom && $activeYear
                              ? $this->service->getByClassroom($classroom, $semester)
                              : collect(),
            'classroom'   => $classroom,
            'activeYear'  => $activeYear,
            'semester'    => $semester,
        ]);
    }

    public function submit(ReportCard $reportCard)
    {
        $user    = request()->user();
        $teacher = $user->teacher;

        abort_if(!$teacher, 403);
        abort_if(
            $reportCard->classroom->homeroom_teacher_id !== $teacher->id,
            403,
            'Hanya wali kelas yang dapat mengajukan persetujuan raport.'
        );
        abort_if(
            !$reportCard->isDraft(),
            422,
            'Hanya raport berstatus draft yang dapat diajukan.'
        );

        $this->service->submitForApproval($reportCard);

        return redirect()->back()->with('success', 'Raport berhasil diajukan untuk persetujuan Kamad.');
    }

    public function export(ReportCard $reportCard)
    {
        $user    = request()->user();
        $teacher = $user->teacher;

        abort_if(!$teacher, 403);
        abort_if(
            $reportCard->classroom->homeroom_teacher_id !== $teacher->id,
            403,
            'Hanya wali kelas yang dapat mengekspor raport.'
        );
        abort_if(
            !$reportCard->isApproved(),
            403,
            'Raport hanya dapat diekspor setelah disetujui Kamad.'
        );

        $reportCard->load(['student', 'classroom.academicYear', 'notes']);

        $reportData    = $this->service->buildReportData(
            $reportCard->student,
            $reportCard->classroom,
            $reportCard->classroom->academicYear,
            $reportCard->semester,
        );
        $schoolSetting    = SchoolSetting::current();
        $verifyUrl        = route('report-cards.verify', $reportCard->verify_code);
        $qrSvg            = (string) QrCode::size(96)->margin(1)->generate($verifyUrl);
        $qrCode           = 'data:image/svg+xml;base64,' . base64_encode($qrSvg);
        $predicateConfigs = \App\Models\PredicateConfig::where('academic_year_id', $reportCard->academic_year_id)
                                                        ->orderBy('min_score')
                                                        ->get();

        $pdf = Pdf::loadView('pdf.report_card', [
            'reportCard'       => $reportCard,
            'student'          => $reportCard->student,
            'classroom'        => $reportCard->classroom,
            'academicYear'     => $reportCard->classroom->academicYear,
            'reportData'       => $reportData,
            'schoolSetting'    => $schoolSetting,
            'teacher'          => $teacher,
            'verifyUrl'        => $verifyUrl,
            'qrCode'           => $qrCode,
            'predicateConfigs' => $predicateConfigs,
        ])->setPaper('a4', 'portrait');

        $filename = 'raport-' . str($reportCard->student->name)->slug() . '-sem' . $reportCard->semester . '.pdf';

        return $pdf->download($filename);
    }

    public function updateNotes(UpdateReportCardNotesRequest $request, ReportCard $reportCard)
    {
        $user      = request()->user();
        $teacher   = $user->teacher;
        $classroom = $reportCard->classroom;

        if ($classroom->homeroom_teacher_id !== $teacher?->id) {
            abort(403, 'Hanya wali kelas yang dapat mengisi catatan raport.');
        }

        $this->service->updateNotes($reportCard, $request->validated());

        return redirect()->back()->with('success', 'Catatan raport berhasil disimpan.');
    }
}
