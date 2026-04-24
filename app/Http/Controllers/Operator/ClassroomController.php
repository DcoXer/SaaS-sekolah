<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClassroomRequest;
use App\Http\Requests\UpdateClassroomRequest;
use App\Models\Classroom;
use App\Models\Teacher;
use App\Services\AcademicYearService;
use App\Services\ClassroomService;
use App\Services\SubjectService;
use App\Services\TeacherService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ClassroomController extends Controller
{
    public function __construct(
        private ClassroomService $service,
        private AcademicYearService $academicYearService,
        private TeacherService $teacherService,
        private SubjectService $subjectService,
    ) {}

    public function index(): Response
    {
        return Inertia::render('Operator/Classroom/Index', [
            'classrooms'    => $this->service->getAll(),
            'academicYears' => $this->academicYearService->getAll(),
        ]);
    }

    public function store(StoreClassroomRequest $request)
    {
        $this->service->create($request->validated());

        return redirect()->back()->with('success', 'Kelas berhasil ditambahkan.');
    }

    public function show(Classroom $classroom): Response
    {
        $classroom = $this->service->getById($classroom);

        return Inertia::render('Operator/Classroom/Show', [
            'classroom' => $classroom,
            'teachers'  => $this->teacherService->getAll(),
            'subjects'  => $this->subjectService->getByGrade($classroom->grade),
        ]);
    }

    public function update(UpdateClassroomRequest $request, Classroom $classroom)
    {
        $this->service->update($classroom, $request->validated());

        return redirect()->back()->with('success', 'Data kelas berhasil diupdate.');
    }

    public function destroy(Classroom $classroom)
    {
        $this->service->delete($classroom);

        return redirect()->back()->with('success', 'Kelas berhasil dihapus.');
    }

    public function availableStudents(Classroom $classroom): \Illuminate\Http\JsonResponse
    {
        $activeYear = $this->academicYearService->getActive();

        abort_if(!$activeYear, 404);

        return response()->json(
            $this->service->getAvailableStudents($classroom, $activeYear)
        );
    }

    public function assignStudents(Request $request, Classroom $classroom)
    {
        $request->validate([
            'student_ids'   => ['required', 'array', 'min:1'],
            'student_ids.*' => ['exists:students,id'],
        ]);

        $activeYear = $this->academicYearService->getActive();

        abort_if(!$activeYear, 404);

        $this->service->assignStudents($classroom, $activeYear, $request->student_ids);

        return redirect()->back()->with('success', 'Siswa berhasil ditambahkan ke rombel.');
    }

    public function assignGuruKelas(Request $request, Classroom $classroom)
    {
        $request->validate([
            'teacher_id' => ['required', 'exists:teachers,id'],
        ]);

        $activeYear = $this->academicYearService->getActive();
        abort_if(!$activeYear, 404, 'Tidak ada tahun ajaran aktif.');

        $teacher = Teacher::findOrFail($request->teacher_id);

        $this->service->assignGuruKelas($classroom, $teacher, $activeYear);

        return redirect()->back()->with('success', 'Guru kelas berhasil di-assign.');
    }

    public function assignWaliKelas(Request $request, Classroom $classroom)
    {
        $request->validate([
            'teacher_id' => ['required', 'exists:teachers,id'],
        ]);

        $activeYear = $this->academicYearService->getActive();
        abort_if(!$activeYear, 404, 'Tidak ada tahun ajaran aktif.');

        $teacher = Teacher::findOrFail($request->teacher_id);

        $this->service->assignWaliKelas($classroom, $teacher, $activeYear);

        return redirect()->back()->with('success', 'Wali kelas berhasil di-assign.');
    }

    public function assignGuruBidang(Request $request, Classroom $classroom)
    {
        $request->validate([
            'teacher_id' => ['required', 'exists:teachers,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
        ]);

        $activeYear = $this->academicYearService->getActive();
        abort_if(!$activeYear, 404, 'Tidak ada tahun ajaran aktif.');

        $teacher = Teacher::findOrFail($request->teacher_id);

        $this->service->assignGuruBidang($classroom, $teacher, $request->subject_id, $activeYear);

        return redirect()->back()->with('success', 'Guru bidang berhasil di-assign ke mapel.');
    }

    public function availableTeachers(Classroom $classroom): \Illuminate\Http\JsonResponse
    {
        $activeYear = $this->academicYearService->getActive();
        abort_if(!$activeYear, 404);

        if ($classroom->grade <= 3) {
            return response()->json([
                'type'     => 'guru_kelas',
                'teachers' => $this->service->getAvailableGuruKelas($activeYear),
            ]);
        }

        return response()->json([
            'type'            => 'guru_bidang',
            'wali_kelas'      => $this->service->getAvailableWaliKelas($classroom, $activeYear),
            'all_guru_bidang' => Teacher::where('type', 'guru_bidang')->with('user')->get(),
        ]);
    }
}