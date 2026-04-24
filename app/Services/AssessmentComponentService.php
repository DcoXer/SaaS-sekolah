<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\AssessmentComponent;
use App\Models\Classroom;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Collection;

class AssessmentComponentService
{
    public function getByClassroomAndSubject(
        Classroom $classroom,
        Subject $subject,
        int $semester
    ): Collection {
        return AssessmentComponent::where('classroom_id', $classroom->id)
                                  ->where('subject_id', $subject->id)
                                  ->where('semester', $semester)
                                  ->orderBy('order')
                                  ->get();
    }

    public function getByClassroom(Classroom $classroom, int $semester): Collection
    {
        return AssessmentComponent::with('subject')
                                  ->where('classroom_id', $classroom->id)
                                  ->where('semester', $semester)
                                  ->orderBy('subject_id')
                                  ->orderBy('order')
                                  ->get();
    }

    public function create(array $data): AssessmentComponent
    {
        return AssessmentComponent::create([
            'academic_year_id' => $data['academic_year_id'],
            'subject_id'       => $data['subject_id'],
            'classroom_id'     => $data['classroom_id'],
            'name'             => $data['name'],
            'type'             => $data['type'],
            'ki'               => $data['type'] === 'numeric' ? ($data['ki'] ?? null) : null,
            'weight'           => $data['weight'],
            'min_score'        => $data['min_score'] ?? 0,
            'max_score'        => $data['max_score'] ?? 100,
            'order'            => $data['order'] ?? 0,
            'semester'         => $data['semester'],
        ]);
    }

    public function update(AssessmentComponent $component, array $data): AssessmentComponent
    {
        $component->update([
            'name'      => $data['name'],
            'type'      => $data['type'],
            'ki'        => $data['type'] === 'numeric' ? ($data['ki'] ?? null) : null,
            'weight'    => $data['weight'],
            'min_score' => $data['min_score'] ?? 0,
            'max_score' => $data['max_score'] ?? 100,
            'order'     => $data['order'] ?? 0,
        ]);

        return $component->fresh();
    }

    public function delete(AssessmentComponent $component): void
    {
        $component->delete();
    }

    public function validateTotalWeight(
        Classroom $classroom,
        Subject $subject,
        int $semester,
        int $newWeight,
        ?int $excludeId = null,
        ?string $ki = null
    ): bool {
        $query = AssessmentComponent::where('classroom_id', $classroom->id)
                                    ->where('subject_id', $subject->id)
                                    ->where('semester', $semester)
                                    ->where('type', 'numeric');

        // Validasi per KI jika ki disediakan
        if ($ki !== null) {
            $query->where('ki', $ki);
        }

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $existingTotal = $query->sum('weight');

        return ($existingTotal + $newWeight) <= 100;
    }
}