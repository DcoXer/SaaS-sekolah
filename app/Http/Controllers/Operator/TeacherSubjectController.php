<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTeacherSubjectRequest;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\TeacherSubject;
use App\Services\AcademicYearService;
use App\Services\ClassroomService;
use App\Services\SubjectService;
use App\Services\TeacherService;
use App\Services\TeacherSubjectService;
use Inertia\Inertia;
use Inertia\Response;

class TeacherSubjectController extends Controller
{
    public function __construct(
        private TeacherSubjectService $service,
        private ClassroomService $classroomService,
        private TeacherService $teacherService,
        private SubjectService $subjectService,
        private AcademicYearService $academicYearService,
    ) {}

    public function index(): Response
    {
        $activeYear = $this->academicYearService->getActive();

        return Inertia::render('Operator/TeacherSubject/Index', [
            'assignments'   => $activeYear
                                ? $this->service->getByAcademicYear($activeYear)
                                : collect(),
            'academicYears' => $this->academicYearService->getAll(),
            'classrooms'    => $this->classroomService->getAll(),
            'teachers'      => $this->teacherService->getAll(),
            'subjects'      => $this->subjectService->getAll(),
        ]);
    }

    public function store(StoreTeacherSubjectRequest $request)
    {
        $this->service->assign($request->validated());

        return redirect()->back()->with('success', 'Guru berhasil di-assign ke mata pelajaran.');
    }

    public function destroy(TeacherSubject $teacherSubject)
    {
        $this->service->unassign($teacherSubject);

        return redirect()->back()->with('success', 'Assignment berhasil dihapus.');
    }
}