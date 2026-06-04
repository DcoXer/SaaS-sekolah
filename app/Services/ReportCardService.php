<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\ReportCard;
use App\Models\ReportCardNote;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReportCardService
{
    public function __construct(
        private StudentAssessmentService $assessmentService,
        private PredicateConfigService $predicateService,
    ) {}

    public function getByClassroom(Classroom $classroom, int $semester): Collection
    {
        return ReportCard::with(['student', 'notes'])
                         ->where('classroom_id', $classroom->id)
                         ->where('semester', $semester)
                         ->get();
    }

    public function getByStudent(
        Student $student,
        AcademicYear $academicYear,
        int $semester
    ): ?ReportCard {
        return ReportCard::with(['notes', 'classroom', 'academicYear'])
                         ->where('student_id', $student->id)
                         ->where('academic_year_id', $academicYear->id)
                         ->where('semester', $semester)
                         ->first();
    }

    public function generateForClass(
        Classroom $classroom,
        AcademicYear $academicYear,
        int $semester
    ): void {
        $students = $classroom->students;

        abort_if($students->isEmpty(), 422, 'Kelas ini belum memiliki siswa. Tambahkan siswa ke kelas terlebih dahulu.');

        DB::transaction(function () use ($students, $classroom, $academicYear, $semester) {
            foreach ($students as $student) {
                ReportCard::firstOrCreate(
                    [
                        'student_id'       => $student->id,
                        'academic_year_id' => $academicYear->id,
                        'semester'         => $semester,
                    ],
                    [
                        'classroom_id' => $classroom->id,
                        'status'       => 'draft',
                    ]
                );
            }
        });
    }

    public function updateNotes(ReportCard $reportCard, array $data): void
    {
        ReportCardNote::updateOrCreate(
            ['report_card_id' => $reportCard->id],
            [
                'homeroom_notes'  => $data['homeroom_notes'] ?? null,
                'principal_notes' => $data['principal_notes'] ?? null,
            ]
        );
    }

    public function submitForApproval(ReportCard $reportCard): void
    {
        $reportCard->update(['status' => 'waiting_approval']);
    }

    public function approve(ReportCard $reportCard, User $approvedBy): void
    {
        $reportCard->update([
            'status'      => 'approved',
            'verify_code' => Str::uuid()->toString(),
            'approved_at' => now(),
            'approved_by' => $approvedBy->id,
        ]);
    }

    public function approveAll(
        Classroom $classroom,
        AcademicYear $academicYear,
        int $semester,
        User $approvedBy
    ): void {
        DB::transaction(function () use ($classroom, $academicYear, $semester, $approvedBy) {
            ReportCard::where('classroom_id', $classroom->id)
                      ->where('academic_year_id', $academicYear->id)
                      ->where('semester', $semester)
                      ->where('status', 'waiting_approval')
                      ->get()
                      ->each(fn($rc) => $this->approve($rc, $approvedBy));
        });
    }

    public function buildReportData(
        Student $student,
        Classroom $classroom,
        AcademicYear $academicYear,
        int $semester
    ): array {
        $subjects = \App\Models\Subject::where('grade', $classroom->grade)->get();

        // Pre-load all components for this classroom+semester (1 query instead of N per subject)
        $allComponents = \App\Models\AssessmentComponent::where('classroom_id', $classroom->id)
            ->where('semester', $semester)
            ->get()
            ->groupBy('subject_id');

        // Pre-load all student assessments for this student+classroom+semester (1 query instead of N×M)
        $allAssessments = \App\Models\StudentAssessment::where('student_id', $student->id)
            ->where('classroom_id', $classroom->id)
            ->where('semester', $semester)
            ->get()
            ->keyBy('assessment_component_id');

        $reportData = [];

        foreach ($subjects as $subject) {
            $subjectComponents = $allComponents->get($subject->id, collect());

            $numericKi3 = $subjectComponents->filter(fn($c) => $c->type === 'numeric' && $c->ki === 'ki3');
            $numericKi4 = $subjectComponents->filter(fn($c) => $c->type === 'numeric' && $c->ki === 'ki4');
            $hasKiComponents = $numericKi3->isNotEmpty() || $numericKi4->isNotEmpty();

            $ki3 = $this->calculateFromPreloaded($numericKi3, $allAssessments, $academicYear);
            $ki4 = $this->calculateFromPreloaded($numericKi4, $allAssessments, $academicYear);

            if (!$hasKiComponents) {
                $numericAll = $subjectComponents->filter(fn($c) => $c->type === 'numeric');
                $ki3 = $this->calculateFromPreloaded($numericAll, $allAssessments, $academicYear);
                $ki4 = ['score' => null, 'predicate' => null];
            }

            $narrativeIds = $subjectComponents->filter(fn($c) => $c->type === 'narrative')->pluck('id');
            $narratives   = $allAssessments->filter(fn($a) => $narrativeIds->contains($a->assessment_component_id))->values();

            $reportData[] = [
                'subject'       => $subject,
                'ki3_score'     => $ki3['score'],
                'ki3_predicate' => $ki3['predicate'],
                'ki4_score'     => $ki4['score'],
                'ki4_predicate' => $ki4['predicate'],
                'narratives'    => $narratives,
            ];
        }

        return $reportData;
    }

    private function calculateFromPreloaded(
        \Illuminate\Support\Collection $components,
        \Illuminate\Support\Collection $assessments,
        AcademicYear $academicYear
    ): array {
        if ($components->isEmpty()) {
            return ['score' => null, 'predicate' => null];
        }

        $totalWeight = $components->sum('weight');

        if ($totalWeight === 0) {
            return ['score' => null, 'predicate' => null];
        }

        $weightedScore = 0;

        foreach ($components as $component) {
            $assessment = $assessments->get($component->id);
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

    public function verifyCode(string $verifyCode): ?ReportCard
    {
        return ReportCard::with(['student', 'classroom.academicYear'])
                         ->where('verify_code', $verifyCode)
                         ->where('status', 'approved')
                         ->first();
    }
}
