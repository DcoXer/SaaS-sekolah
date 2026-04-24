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
    public function __construct(private StudentAssessmentService $assessmentService) {}

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

        $reportData = [];

        foreach ($subjects as $subject) {
            $ki3 = $this->assessmentService->calculateFinalScore(
                $student, $classroom, $subject->id, $semester, $academicYear, 'ki3'
            );
            $ki4 = $this->assessmentService->calculateFinalScore(
                $student, $classroom, $subject->id, $semester, $academicYear, 'ki4'
            );

            // Fallback: jika tidak ada komponen ber-ki, hitung tanpa filter ki
            if ($ki3['score'] === null && $ki4['score'] === null) {
                $fallback = $this->assessmentService->calculateFinalScore(
                    $student, $classroom, $subject->id, $semester, $academicYear
                );
                $ki3 = $fallback;
                $ki4 = ['score' => null, 'predicate' => null];
            }

            $narratives = \App\Models\StudentAssessment::whereHas('assessmentComponent', function ($q) use ($subject, $semester) {
                $q->where('subject_id', $subject->id)
                  ->where('semester', $semester)
                  ->where('type', 'narrative');
            })
            ->where('student_id', $student->id)
            ->where('classroom_id', $classroom->id)
            ->get();

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

    public function verifyCode(string $verifyCode): ?ReportCard
    {
        return ReportCard::with(['student', 'classroom.academicYear'])
                         ->where('verify_code', $verifyCode)
                         ->where('status', 'approved')
                         ->first();
    }
}
