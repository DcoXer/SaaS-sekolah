<?php

namespace App\Http\Controllers\Kamad;

use App\Http\Controllers\Controller;
use App\Services\AcademicYearService;
use App\Services\TeacherHonorariumService;
use Inertia\Inertia;
use Inertia\Response;

class HonorariumController extends Controller
{
    public function __construct(
        private TeacherHonorariumService $service,
        private AcademicYearService $academicYearService,
    ) {}

    public function index(): Response
    {
        $filters = [
            'month'      => request('month'),
            'year'       => request('year'),
            'status'     => request('status'),
            'teacher_id' => request('teacher_id'),
        ];

        $honorariums   = $this->service->getAll($filters);
        $academicYears = $this->academicYearService->getAll();

        return Inertia::render('Kamad/Honorarium/Index', [
            'honorariums'   => $honorariums,
            'academicYears' => $academicYears,
            'filters'       => $filters,
        ]);
    }
}
