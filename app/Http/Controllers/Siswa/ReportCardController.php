<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Services\AcademicYearService;
use App\Services\ReportCardService;
use Inertia\Inertia;
use Inertia\Response;

class ReportCardController extends Controller
{
    public function __construct(
        private ReportCardService $service,
        private AcademicYearService $academicYearService,
    ) {}

    public function index(): Response
    {
        $user       = request()->user();
        $student    = $user->student;
        $activeYear = $this->academicYearService->getActive();

        $reportCards = [];

        if ($student && $activeYear) {
            foreach ([1, 2] as $semester) {
                $reportCard = $this->service->getByStudent($student, $activeYear, $semester);

                if ($reportCard?->isApproved()) {
                    $reportCards[$semester] = $reportCard;
                }
            }
        }

        return Inertia::render('Siswa/ReportCard/Index', [
            'reportCards' => $reportCards,
            'activeYear'  => $activeYear,
        ]);
    }

    public function show(int $semester): Response
    {
        $user       = request()->user();
        $student    = $user->student;
        $activeYear = $this->academicYearService->getActive();

        abort_if(!$student || !$activeYear, 404);

        $reportCard = $this->service->getByStudent($student, $activeYear, $semester);

        abort_if(!$reportCard || !$reportCard->isApproved(), 404);

        $reportData = $this->service->buildReportData(
            $student,
            $reportCard->classroom,
            $activeYear,
            $semester
        );

        return Inertia::render('Siswa/ReportCard/Show', [
            'reportCard' => $reportCard,
            'reportData' => $reportData,
            'activeYear' => $activeYear,
        ]);
    }
}