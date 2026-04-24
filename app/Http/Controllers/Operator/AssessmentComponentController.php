<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAssessmentComponentRequest;
use App\Http\Requests\UpdateAssessmentComponentRequest;
use App\Models\AssessmentComponent;
use App\Services\AcademicYearService;
use App\Services\AssessmentComponentService;
use App\Services\ClassroomService;
use App\Services\SubjectService;
use Inertia\Inertia;
use Inertia\Response;

class AssessmentComponentController extends Controller
{
    public function __construct(
        private AssessmentComponentService $service,
        private AcademicYearService $academicYearService,
        private ClassroomService $classroomService,
        private SubjectService $subjectService,
    ) {}

    public function index(): Response
    {
        $activeYear = $this->academicYearService->getActive();

        return Inertia::render('Operator/AssessmentComponent/Index', [
            'components'    => $activeYear
                                ? $this->service->getByClassroom(
                                    request('classroom_id')
                                        ? \App\Models\Classroom::find(request('classroom_id'))
                                        : $this->classroomService->getAll()->first(),
                                    request('semester', 1)
                                  )
                                : collect(),
            'academicYears' => $this->academicYearService->getAll(),
            'classrooms'    => $this->classroomService->getAll(),
            'subjects'      => $this->subjectService->getAll(),
        ]);
    }

    public function store(StoreAssessmentComponentRequest $request)
    {
        $validated = $request->validated();

        // Validasi total weight tidak melebihi 100
        if ($validated['type'] === 'numeric') {
            $classroom = \App\Models\Classroom::find($validated['classroom_id']);
            $subject   = \App\Models\Subject::find($validated['subject_id']);

            $valid = $this->service->validateTotalWeight(
                $classroom,
                $subject,
                $validated['semester'],
                $validated['weight'],
                null,
                $validated['ki'] ?? null
            );

            if (!$valid) {
                $kiLabel = match($validated['ki'] ?? null) {
                    'ki3'   => 'Pengetahuan (KI 3)',
                    'ki4'   => 'Keterampilan (KI 4)',
                    default => 'numerik',
                };
                return redirect()->back()->withErrors([
                    'weight' => "Total bobot komponen {$kiLabel} tidak boleh melebihi 100%.",
                ]);
            }
        }

        $this->service->create($validated);

        return redirect()->back()->with('success', 'Komponen penilaian berhasil ditambahkan.');
    }

    public function update(UpdateAssessmentComponentRequest $request, AssessmentComponent $assessmentComponent)
    {
        $validated = $request->validated();

        if ($validated['type'] === 'numeric') {
            $ki    = $validated['ki'] ?? $assessmentComponent->ki;
            $valid = $this->service->validateTotalWeight(
                $assessmentComponent->classroom,
                $assessmentComponent->subject,
                $assessmentComponent->semester,
                $validated['weight'],
                $assessmentComponent->id,
                $ki
            );

            if (!$valid) {
                $kiLabel = match($ki) {
                    'ki3'   => 'Pengetahuan (KI 3)',
                    'ki4'   => 'Keterampilan (KI 4)',
                    default => 'numerik',
                };
                return redirect()->back()->withErrors([
                    'weight' => "Total bobot komponen {$kiLabel} tidak boleh melebihi 100%.",
                ]);
            }
        }

        $this->service->update($assessmentComponent, $validated);

        return redirect()->back()->with('success', 'Komponen penilaian berhasil diupdate.');
    }

    public function destroy(AssessmentComponent $assessmentComponent)
    {
        $this->service->delete($assessmentComponent);

        return redirect()->back()->with('success', 'Komponen penilaian berhasil dihapus.');
    }
}