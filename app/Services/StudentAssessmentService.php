<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\AssessmentComponent;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\StudentAssessment;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class StudentAssessmentService
{
    public function __construct(private PredicateConfigService $predicateService) {}

    public function getByStudentAndClassroom(
        Student $student,
        Classroom $classroom,
        int $semester
    ): Collection {
        return StudentAssessment::with('assessmentComponent.subject')
                                ->where('student_id', $student->id)
                                ->where('classroom_id', $classroom->id)
                                ->where('semester', $semester)
                                ->get();
    }

    public function getByClassroomAndComponent(
        Classroom $classroom,
        AssessmentComponent $component
    ): Collection {
        return StudentAssessment::with('student')
                                ->where('classroom_id', $classroom->id)
                                ->where('assessment_component_id', $component->id)
                                ->get();
    }

    public function inputScore(
        Student $student,
        AssessmentComponent $component,
        User $inputBy,
        array $data
    ): StudentAssessment {
        return StudentAssessment::updateOrCreate(
            [
                'student_id'              => $student->id,
                'assessment_component_id' => $component->id,
                'semester'                => $data['semester'],
            ],
            [
                'academic_year_id' => $component->academic_year_id,
                'classroom_id'     => $component->classroom_id,
                'input_by'         => $inputBy->id,
                'score'            => $data['score'] ?? null,
                'predicate'        => $data['predicate'] ?? null,
                'narrative'        => $data['narrative'] ?? null,
            ]
        );
    }

    public function bulkInputScore(
        Classroom $classroom,
        AssessmentComponent $component,
        User $inputBy,
        array $scores
    ): void {
        DB::transaction(function () use ($classroom, $component, $inputBy, $scores) {
            foreach ($scores as $score) {
                StudentAssessment::updateOrCreate(
                    [
                        'student_id'              => $score['student_id'],
                        'assessment_component_id' => $component->id,
                        'semester'                => $component->semester,
                    ],
                    [
                        'academic_year_id' => $component->academic_year_id,
                        'classroom_id'     => $classroom->id,
                        'input_by'         => $inputBy->id,
                        'score'            => $score['score'] ?? null,
                        'predicate'        => $score['predicate'] ?? null,
                        'narrative'        => $score['narrative'] ?? null,
                    ]
                );
            }
        });
    }

    public function calculateFinalScore(
        Student $student,
        Classroom $classroom,
        int $subjectId,
        int $semester,
        AcademicYear $academicYear,
        ?string $ki = null
    ): array {
        $query = AssessmentComponent::where('classroom_id', $classroom->id)
                                    ->where('subject_id', $subjectId)
                                    ->where('semester', $semester)
                                    ->where('type', 'numeric');

        if ($ki !== null) {
            $query->where('ki', $ki);
        }

        $components = $query->get();

        if ($components->isEmpty()) {
            return ['score' => null, 'predicate' => null];
        }

        $totalWeight = $components->sum('weight');

        if ($totalWeight === 0) {
            return ['score' => null, 'predicate' => null];
        }

        $weightedScore = 0;

        foreach ($components as $component) {
            $assessment = StudentAssessment::where('student_id', $student->id)
                                           ->where('assessment_component_id', $component->id)
                                           ->where('semester', $semester)
                                           ->first();

            if ($assessment?->score !== null) {
                $weightedScore += ($assessment->score * $component->weight);
            }
        }

        $finalScore = round($weightedScore / $totalWeight, 2);
        $predicate  = $this->predicateService->getPredicateByScore($academicYear, (int) $finalScore);

        return [
            'score'     => $finalScore,
            'predicate' => $predicate,
        ];
    }
}