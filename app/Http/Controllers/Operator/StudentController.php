<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Services\AcademicYearService;
use App\Services\ClassroomService;
use App\Services\StudentService;
use Inertia\Inertia;
use Inertia\Response;

class StudentController extends Controller
{
    public function __construct(
        private StudentService $service,
        private ClassroomService $classroomService,
        private AcademicYearService $academicYearService,
    ) {}

    public function index(): Response
    {
        return Inertia::render('Operator/Student/Index', [
            'students' => $this->service->getAll(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Operator/Student/Create');
    }

    public function store(StoreStudentRequest $request)
    {
        $this->service->create($request->validated());

        return redirect()->route('operator.students.index')->with('success', 'Data siswa berhasil ditambahkan.');
    }

    public function show(Student $student): Response
    {
        return Inertia::render('Operator/Student/Show', [
            'student'       => $this->service->getById($student),
            'academicYears' => $this->academicYearService->getAll(),
            'classrooms'    => $this->classroomService->getAll(),
        ]);
    }

    public function update(UpdateStudentRequest $request, Student $student)
    {
        $this->service->update($student, $request->validated());

        return redirect()->back()->with('success', 'Data siswa berhasil diupdate.');
    }

    public function destroy(Student $student)
    {
        $this->service->delete($student);

        return redirect()->back()->with('success', 'Data siswa berhasil dihapus.');
    }

    public function assignClassroom(Request $request, Student $student)
    {
        $request->validate([
            'classroom_id'     => ['required', 'exists:classrooms,id'],
            'academic_year_id' => ['required', 'exists:academic_years,id'],
        ]);

        $this->service->assignToClassroom(
            $student,
            $request->classroom_id,
            $request->academic_year_id
        );

        return redirect()->back()->with('success', 'Siswa berhasil dipindahkan ke kelas.');
    }
}