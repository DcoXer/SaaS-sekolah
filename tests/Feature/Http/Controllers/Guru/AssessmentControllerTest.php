<?php

namespace Tests\Feature\Http\Controllers\Guru;

use App\Models\AcademicYear;
use App\Models\AssessmentComponent;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherSubject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssessmentControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $guru;
    private User $operator;
    private Teacher $teacher;
    private AcademicYear $academicYear;
    private Classroom $classroom;
    private Subject $subject;
    private AssessmentComponent $component;
    private Student $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->operator = User::factory()->create();
        $this->operator->assignRole('operator');

        $guruUser = User::factory()->create();
        $guruUser->assignRole('guru');
        $this->guru = $guruUser;

        $this->teacher = Teacher::create([
            'user_id' => $guruUser->id,
            'gender'  => 'L',
            'type'    => 'guru_bidang',
        ]);

        $this->academicYear = AcademicYear::create([
            'name'       => '2024/2025',
            'start_date' => '2024-07-15',
            'end_date'   => '2025-06-30',
            'status'     => 'active',
        ]);

        $this->classroom = Classroom::create([
            'academic_year_id' => $this->academicYear->id,
            'name'             => 'Kelas 4A',
            'grade'            => 4,
        ]);

        $this->subject = Subject::create([
            'name'  => 'Matematika',
            'grade' => 4,
        ]);

        $this->student = Student::create([
            'nis'    => '001',
            'name'   => 'Ahmad',
            'gender' => 'L',
            'grade'  => 4,
            'status' => 'active',
        ]);

        $this->classroom->students()->attach($this->student->id, ['academic_year_id' => $this->academicYear->id]);

        TeacherSubject::create([
            'teacher_id'       => $this->teacher->id,
            'classroom_id'     => $this->classroom->id,
            'subject_id'       => $this->subject->id,
            'academic_year_id' => $this->academicYear->id,
        ]);

        $this->component = AssessmentComponent::create([
            'academic_year_id' => $this->academicYear->id,
            'classroom_id'     => $this->classroom->id,
            'subject_id'       => $this->subject->id,
            'name'             => 'UTS',
            'type'             => 'numeric',
            'weight'           => 40,
            'semester'         => 1,
            'order'            => 1,
        ]);
    }

    public function test_guru_can_view_assessment_index(): void
    {
        $response = $this->actingAs($this->guru)
                         ->get(route('guru.assessments.index'));

        $response->assertStatus(200);
    }

    public function test_guest_cannot_view_assessments(): void
    {
        $response = $this->get(route('guru.assessments.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_operator_cannot_view_guru_assessments(): void
    {
        $response = $this->actingAs($this->operator)
                         ->get(route('guru.assessments.index'));

        $response->assertStatus(403);
    }

    public function test_guru_can_view_assessment_show(): void
    {
        $response = $this->actingAs($this->guru)
                         ->get(route('guru.assessments.show', [$this->classroom, $this->component]));

        $response->assertStatus(200);
    }

    public function test_guru_cannot_view_assessment_for_unassigned_class(): void
    {
        $otherClassroom = Classroom::create([
            'academic_year_id' => $this->academicYear->id,
            'name'             => 'Kelas 5A',
            'grade'            => 5,
        ]);
        $otherSubject = Subject::create(['name' => 'IPA', 'grade' => 5]);
        $otherComponent = AssessmentComponent::create([
            'academic_year_id' => $this->academicYear->id,
            'classroom_id'     => $otherClassroom->id,
            'subject_id'       => $otherSubject->id,
            'name'             => 'UTS',
            'type'             => 'numeric',
            'weight'           => 40,
            'semester'         => 1,
            'order'            => 1,
        ]);

        $response = $this->actingAs($this->guru)
                         ->get(route('guru.assessments.show', [$otherClassroom, $otherComponent]));

        $response->assertStatus(403);
    }

    public function test_guru_can_bulk_store_scores(): void
    {
        $response = $this->actingAs($this->guru)
                         ->post(route('guru.assessments.bulk-store', $this->component), [
                             'scores' => [
                                 ['student_id' => $this->student->id, 'score' => 85],
                             ],
                         ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    public function test_bulk_store_fails_without_scores(): void
    {
        $response = $this->actingAs($this->guru)
                         ->post(route('guru.assessments.bulk-store', $this->component), [
                             'scores' => [],
                         ]);

        $response->assertSessionHasErrors('scores');
    }

    public function test_guru_cannot_bulk_store_for_unassigned_class(): void
    {
        $otherUser = User::factory()->create();
        $otherUser->assignRole('guru');
        $otherTeacher = Teacher::create([
            'user_id' => $otherUser->id,
            'gender'  => 'L',
            'type'    => 'guru_bidang',
        ]);

        $response = $this->actingAs($otherUser)
                         ->post(route('guru.assessments.bulk-store', $this->component), [
                             'scores' => [
                                 ['student_id' => $this->student->id, 'score' => 85],
                             ],
                         ]);

        $response->assertStatus(403);
    }
}
