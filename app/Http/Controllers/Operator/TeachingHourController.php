<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Http\Requests\SetTeachingHourRequest;
use App\Models\Teacher;
use App\Models\TeacherTeachingHour;
use App\Services\AcademicYearService;
use App\Services\TeachingHourService;
use Inertia\Inertia;
use Inertia\Response;

class TeachingHourController extends Controller
{
    public function __construct(
        private TeachingHourService $service,
        private AcademicYearService $academicYearService,
    ) {}

    public function index(): Response
    {
        $academicYears = $this->academicYearService->getAll();
        $activeYear    = $this->academicYearService->getActive();

        $selectedYearId = request('academic_year_id', $activeYear?->id);
        $selectedYear   = $academicYears->firstWhere('id', $selectedYearId);

        $data = $selectedYear
            ? $this->service->getAllByYear($selectedYear)
            : collect();

        return Inertia::render('Operator/TeachingHour/Index', [
            'academicYears'    => $academicYears,
            'selectedYearId'   => $selectedYearId,
            'teachingHourData' => $data,
        ]);
    }

    public function store(SetTeachingHourRequest $request)
    {
        $validated = $request->validated();
        $teacher   = Teacher::findOrFail($validated['teacher_id']);
        $year      = \App\Models\AcademicYear::findOrFail($validated['academic_year_id']);

        $this->service->set($teacher, $year, $validated);

        return redirect()->back()->with('success', 'Jam pelajaran berhasil disimpan.');
    }

    public function destroy(TeacherTeachingHour $teachingHour)
    {
        $this->service->delete($teachingHour);

        return redirect()->back()->with('success', 'Jam pelajaran berhasil dihapus.');
    }
}
