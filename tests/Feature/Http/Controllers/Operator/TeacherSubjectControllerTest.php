<?php

namespace Tests\Feature\Http\Controllers\Operator;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherSubject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherSubjectControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $operator;
    private User $kamad;
    private AcademicYear $academicYear;
    private Classroom $classroom;
    private Teacher $teacher;
    private Subject $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->operator = User::factory()->create();
        $this->operator->assignRole('operator');

        $this->kamad = User::factory()->create();
        $this->kamad->assignRole('kamad');

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

        $user = User::factory()->create();
        $this->teacher = Teacher::create([
            'user_id' => $user->id,
            'gender'  => 'L',
        ]);

        $this->subject = Subject::create([
            'name'  => 'Matematika',
            'grade' => 1,
        ]);
    }

    public function test_operator_can_view_teacher_subjects(): void
    {
        $response = $this->actingAs($this->operator)
                         ->get(route('operator.teacher-subjects.index'));

        $response->assertStatus(200);
    }

    public function test_guest_cannot_view_teacher_subjects(): void
    {
        $response = $this->get(route('operator.teacher-subjects.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_operator_can_assign_teacher_to_subject(): void
    {
        $response = $this->actingAs($this->operator)
                         ->post(route('operator.teacher-subjects.store'), [
                             'teacher_id'       => $this->teacher->id,
                             'subject_id'       => $this->subject->id,
                             'classroom_id'     => $this->classroom->id,
                             'academic_year_id' => $this->academicYear->id,
                         ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('teacher_subjects', [
            'teacher_id' => $this->teacher->id,
            'subject_id' => $this->subject->id,
        ]);
    }

    public function test_store_fails_with_invalid_teacher(): void
    {
        $response = $this->actingAs($this->operator)
                         ->post(route('operator.teacher-subjects.store'), [
                             'teacher_id'       => 999, // invalid
                             'subject_id'       => $this->subject->id,
                             'classroom_id'     => $this->classroom->id,
                             'academic_year_id' => $this->academicYear->id,
                         ]);

        $response->assertSessionHasErrors('teacher_id');
    }

    public function test_operator_can_delete_teacher_subject(): void
    {
        $assignment = TeacherSubject::create([
            'teacher_id'       => $this->teacher->id,
            'subject_id'       => $this->subject->id,
            'classroom_id'     => $this->classroom->id,
            'academic_year_id' => $this->academicYear->id,
        ]);

        $response = $this->actingAs($this->operator)
                         ->delete(route('operator.teacher-subjects.destroy', $assignment));

        $response->assertRedirect();
        $this->assertDatabaseMissing('teacher_subjects', ['id' => $assignment->id]);
    }

    public function test_kamad_cannot_assign_teacher_to_subject(): void
    {
        $response = $this->actingAs($this->kamad)
                         ->post(route('operator.teacher-subjects.store'), [
                             'teacher_id'       => $this->teacher->id,
                             'subject_id'       => $this->subject->id,
                             'classroom_id'     => $this->classroom->id,
                             'academic_year_id' => $this->academicYear->id,
                         ]);

        $response->assertStatus(403);
    }
}