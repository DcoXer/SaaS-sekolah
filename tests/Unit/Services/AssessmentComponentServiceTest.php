<?php

namespace Tests\Unit\Services;

use App\Models\AcademicYear;
use App\Models\AssessmentComponent;
use App\Models\Classroom;
use App\Models\Subject;
use App\Services\AssessmentComponentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssessmentComponentServiceTest extends TestCase
{
    use RefreshDatabase;

    private AssessmentComponentService $service;
    private AcademicYear $academicYear;
    private Classroom $classroom;
    private Subject $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AssessmentComponentService();

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
    }

    public function test_can_create_assessment_component(): void
    {
        $component = $this->service->create([
            'academic_year_id' => $this->academicYear->id,
            'subject_id'       => $this->subject->id,
            'classroom_id'     => $this->classroom->id,
            'name'             => 'Nilai Harian',
            'type'             => 'numeric',
            'weight'           => 30,
            'min_score'        => 0,
            'max_score'        => 100,
            'order'            => 1,
            'semester'         => 1,
        ]);

        $this->assertInstanceOf(AssessmentComponent::class, $component);
        $this->assertDatabaseHas('assessment_components', ['name' => 'Nilai Harian']);
    }

    public function test_can_update_assessment_component(): void
    {
        $component = $this->service->create([
            'academic_year_id' => $this->academicYear->id,
            'subject_id'       => $this->subject->id,
            'classroom_id'     => $this->classroom->id,
            'name'             => 'Nilai Harian',
            'type'             => 'numeric',
            'weight'           => 30,
            'semester'         => 1,
        ]);

        $updated = $this->service->update($component, [
            'name'   => 'Nilai Harian Updated',
            'type'   => 'numeric',
            'weight' => 40,
        ]);

        $this->assertEquals('Nilai Harian Updated', $updated->name);
        $this->assertEquals(40, $updated->weight);
    }

    public function test_can_delete_assessment_component(): void
    {
        $component = $this->service->create([
            'academic_year_id' => $this->academicYear->id,
            'subject_id'       => $this->subject->id,
            'classroom_id'     => $this->classroom->id,
            'name'             => 'Nilai Harian',
            'type'             => 'numeric',
            'weight'           => 30,
            'semester'         => 1,
        ]);

        $this->service->delete($component);

        $this->assertDatabaseMissing('assessment_components', ['id' => $component->id]);
    }

    public function test_validate_total_weight_returns_true_when_valid(): void
    {
        $this->service->create([
            'academic_year_id' => $this->academicYear->id,
            'subject_id'       => $this->subject->id,
            'classroom_id'     => $this->classroom->id,
            'name'             => 'Nilai Harian',
            'type'             => 'numeric',
            'weight'           => 30,
            'semester'         => 1,
        ]);

        $valid = $this->service->validateTotalWeight(
            $this->classroom,
            $this->subject,
            1,
            40 // 30 + 40 = 70, masih valid
        );

        $this->assertTrue($valid);
    }

    public function test_validate_total_weight_returns_false_when_exceeds_100(): void
    {
        $this->service->create([
            'academic_year_id' => $this->academicYear->id,
            'subject_id'       => $this->subject->id,
            'classroom_id'     => $this->classroom->id,
            'name'             => 'Nilai Harian',
            'type'             => 'numeric',
            'weight'           => 70,
            'semester'         => 1,
        ]);

        $valid = $this->service->validateTotalWeight(
            $this->classroom,
            $this->subject,
            1,
            40 // 70 + 40 = 110, invalid
        );

        $this->assertFalse($valid);
    }

    public function test_get_by_classroom_returns_correct_components(): void
    {
        $this->service->create([
            'academic_year_id' => $this->academicYear->id,
            'subject_id'       => $this->subject->id,
            'classroom_id'     => $this->classroom->id,
            'name'             => 'Nilai Harian',
            'type'             => 'numeric',
            'weight'           => 30,
            'semester'         => 1,
        ]);

        $this->service->create([
            'academic_year_id' => $this->academicYear->id,
            'subject_id'       => $this->subject->id,
            'classroom_id'     => $this->classroom->id,
            'name'             => 'UTS',
            'type'             => 'numeric',
            'weight'           => 30,
            'semester'         => 2, // semester berbeda
        ]);

        $components = $this->service->getByClassroom($this->classroom, 1);

        $this->assertCount(1, $components);
        $this->assertEquals('Nilai Harian', $components->first()->name);
    }
}