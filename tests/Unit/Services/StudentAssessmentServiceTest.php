<?php

namespace Tests\Unit\Services;

use App\Models\AcademicYear;
use App\Models\AssessmentComponent;
use App\Models\Classroom;
use App\Models\PredicateConfig;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use App\Services\PredicateConfigService;
use App\Services\StudentAssessmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentAssessmentServiceTest extends TestCase
{
    use RefreshDatabase;

    private StudentAssessmentService $service;
    private AcademicYear $academicYear;
    private Classroom $classroom;
    private Subject $subject;
    private Student $student;
    private User $teacher;
    private AssessmentComponent $component;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new StudentAssessmentService(new PredicateConfigService());

        $this->academicYear = AcademicYear::create([
            'name'       => '2024/2025',
            'start_date' => '2024-07-15',
            'end_date'   => '2025-06-30',
            'status'     => 'active',
        ]);

        $this->classroom = Classroom::create([
            'academic_year_id' => $this->academicYear->id,
            'name'             => 'Kelas 1A',
            'grade'            => 1,
        ]);

        $this->subject = Subject::create([
            'name'  => 'Matematika',
            'grade' => 1,
        ]);

        $this->student = Student::create([
            'nis'    => '001',
            'name'   => 'Ahmad',
            'gender' => 'L',
            'status' => 'active',
        ]);

        $this->teacher = User::factory()->create();
        $this->teacher->assignRole('guru');

        $this->component = AssessmentComponent::create([
            'academic_year_id' => $this->academicYear->id,
            'subject_id'       => $this->subject->id,
            'classroom_id'     => $this->classroom->id,
            'name'             => 'Nilai Harian',
            'type'             => 'numeric',
            'weight'           => 100,
            'min_score'        => 0,
            'max_score'        => 100,
            'order'            => 1,
            'semester'         => 1,
        ]);
    }

    public function test_can_input_score(): void
    {
        $assessment = $this->service->inputScore(
            $this->student,
            $this->component,
            $this->teacher,
            ['semester' => 1, 'score' => 85]
        );

        $this->assertDatabaseHas('student_assessments', [
            'student_id' => $this->student->id,
            'score'      => 85,
        ]);
    }

    public function test_input_score_is_idempotent(): void
    {
        $this->service->inputScore(
            $this->student,
            $this->component,
            $this->teacher,
            ['semester' => 1, 'score' => 85]
        );

        $this->service->inputScore(
            $this->student,
            $this->component,
            $this->teacher,
            ['semester' => 1, 'score' => 90]
        );

        $this->assertCount(1, \App\Models\StudentAssessment::all());
        $this->assertDatabaseHas('student_assessments', ['score' => 90]);
    }

    public function test_can_bulk_input_scores(): void
    {
        $student2 = Student::create([
            'nis'    => '002',
            'name'   => 'Budi',
            'gender' => 'L',
            'status' => 'active',
        ]);

        $this->service->bulkInputScore(
            $this->classroom,
            $this->component,
            $this->teacher,
            [
                ['student_id' => $this->student->id, 'score' => 85],
                ['student_id' => $student2->id,      'score' => 90],
            ]
        );

        $this->assertCount(2, \App\Models\StudentAssessment::all());
    }

    public function test_calculate_final_score_with_single_component(): void
    {
        $this->service->inputScore(
            $this->student,
            $this->component,
            $this->teacher,
            ['semester' => 1, 'score' => 85]
        );

        $result = $this->service->calculateFinalScore(
            $this->student,
            $this->classroom,
            $this->subject->id,
            1,
            $this->academicYear
        );

        $this->assertEquals(85, $result['score']);
    }

    public function test_calculate_final_score_with_weighted_components(): void
    {
        // Update component weight to 40%
        $this->component->update(['weight' => 40]);

        // Tambah UTS 60%
        $uts = AssessmentComponent::create([
            'academic_year_id' => $this->academicYear->id,
            'subject_id'       => $this->subject->id,
            'classroom_id'     => $this->classroom->id,
            'name'             => 'UTS',
            'type'             => 'numeric',
            'weight'           => 60,
            'min_score'        => 0,
            'max_score'        => 100,
            'order'            => 2,
            'semester'         => 1,
        ]);

        // Input nilai harian 80, UTS 90
        $this->service->inputScore($this->student, $this->component, $this->teacher, ['semester' => 1, 'score' => 80]);
        $this->service->inputScore($this->student, $uts, $this->teacher, ['semester' => 1, 'score' => 90]);

        $result = $this->service->calculateFinalScore(
            $this->student,
            $this->classroom,
            $this->subject->id,
            1,
            $this->academicYear
        );

        // (80 * 40 + 90 * 60) / 100 = 86
        $this->assertEquals(86, $result['score']);
    }

    public function test_calculate_final_score_includes_predicate(): void
    {
        PredicateConfig::create([
            'academic_year_id' => $this->academicYear->id,
            'min_score'        => 80,
            'max_score'        => 100,
            'predicate'        => 'A',
        ]);

        $this->service->inputScore(
            $this->student,
            $this->component,
            $this->teacher,
            ['semester' => 1, 'score' => 90]
        );

        $result = $this->service->calculateFinalScore(
            $this->student,
            $this->classroom,
            $this->subject->id,
            1,
            $this->academicYear
        );

        $this->assertEquals('A', $result['predicate']);
    }

    public function test_calculate_final_score_returns_null_when_no_components(): void
    {
        $result = $this->service->calculateFinalScore(
            $this->student,
            $this->classroom,
            999, // subject ga ada
            1,
            $this->academicYear
        );

        $this->assertNull($result['score']);
        $this->assertNull($result['predicate']);
    }
}