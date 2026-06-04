<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Http\Requests\BulkInputScoreRequest;
use App\Models\AssessmentComponent;
use App\Models\Classroom;
use App\Models\TeacherSubject;
use App\Services\AssessmentComponentService;
use App\Services\StudentAssessmentService;
use App\Services\TeacherSubjectService;
use App\Services\AcademicYearService;
use Inertia\Inertia;
use Inertia\Response;

class AssessmentController extends Controller
{
    public function __construct(
        private StudentAssessmentService $service,
        private AssessmentComponentService $componentService,
        private TeacherSubjectService $teacherSubjectService,
        private AcademicYearService $academicYearService,
    ) {}

    public function index(): Response
    {
        $user    = request()->user();
        $teacher = $user->teacher;

        $assignments = $teacher
            ? $this->teacherSubjectService->getByTeacher($teacher)
            : collect();

        // Batch-load all assessment components in one query to avoid N+1
        if ($assignments->isNotEmpty()) {
            $classroomIds = $assignments->pluck('classroom_id')->unique()->values();
            $subjectIds   = $assignments->pluck('subject_id')->unique()->values();

            $allComponents = AssessmentComponent::whereIn('classroom_id', $classroomIds)
                ->whereIn('subject_id', $subjectIds)
                ->orderBy('semester')
                ->orderBy('order')
                ->get()
                ->groupBy(fn($c) => $c->classroom_id . '-' . $c->subject_id);

            $assignments = $assignments->map(function ($assignment) use ($allComponents) {
                $key = $assignment->classroom_id . '-' . $assignment->subject_id;
                $assignment->setRelation('components', $allComponents->get($key, collect()));
                return $assignment;
            });
        }

        return Inertia::render('Guru/Assessment/Index', [
            'assignments' => $assignments,
        ]);
    }

    public function show(Classroom $classroom, AssessmentComponent $assessmentComponent): Response
    {
        $user    = request()->user();
        $teacher = $user->teacher;

        abort_if(!$teacher, 403);

        $assigned = TeacherSubject::where('teacher_id', $teacher->id)
                                  ->where('classroom_id', $classroom->id)
                                  ->where('subject_id', $assessmentComponent->subject_id)
                                  ->exists();

        abort_if(!$assigned, 403, 'Anda tidak memiliki akses ke kelas atau mapel ini.');

        $scores = $this->service->getByClassroomAndComponent($classroom, $assessmentComponent);

        return Inertia::render('Guru/Assessment/Show', [
            'classroom'           => $classroom->load('students'),
            'assessmentComponent' => $assessmentComponent->load('subject'),
            'scores'              => $scores,
        ]);
    }

    public function bulkStore(BulkInputScoreRequest $request, AssessmentComponent $assessmentComponent)
    {
        $user      = request()->user();
        $teacher   = $user->teacher;
        $classroom = $assessmentComponent->classroom;

        abort_if(!$teacher, 403);

        // Pastikan tahun ajaran kelas ini masih aktif — cegah input nilai ke tahun yang sudah closed
        abort_if(
            $classroom->academicYear?->status !== 'active',
            403,
            'Tidak dapat input nilai untuk tahun ajaran yang sudah tidak aktif.'
        );

        $assigned = TeacherSubject::where('teacher_id', $teacher->id)
                                  ->where('classroom_id', $classroom->id)
                                  ->where('subject_id', $assessmentComponent->subject_id)
                                  ->exists();

        abort_if(!$assigned, 403, 'Anda tidak memiliki akses ke kelas atau mapel ini.');

        $this->service->bulkInputScore(
            $classroom,
            $assessmentComponent,
            $user,
            $request->validated()['scores']
        );

        return redirect()->back()->with('success', 'Nilai berhasil disimpan.');
    }
}