<?php

namespace Tests\Feature\Http\Controllers\Operator;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClassroomControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $operator;
    private User $kamad;
    private AcademicYear $academicYear;

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
    }

    public function test_operator_can_view_classrooms(): void
    {
        $response = $this->actingAs($this->operator)
                         ->get(route('operator.classrooms.index'));

        $response->assertStatus(200);
    }

    public function test_guest_cannot_view_classrooms(): void
    {
        $response = $this->get(route('operator.classrooms.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_operator_can_create_classroom(): void
    {
        $response = $this->actingAs($this->operator)
                         ->post(route('operator.classrooms.store'), [
                             'academic_year_id' => $this->academicYear->id,
                             'name'             => 'Kelas 1A',
                             'grade'            => 1,
                         ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('classrooms', ['name' => 'Kelas 1A']);
    }

    public function test_store_fails_with_invalid_grade(): void
    {
        $response = $this->actingAs($this->operator)
                         ->post(route('operator.classrooms.store'), [
                             'academic_year_id' => $this->academicYear->id,
                             'name'             => 'Kelas 7A',
                             'grade'            => 7, // invalid
                         ]);

        $response->assertSessionHasErrors('grade');
    }

    public function test_operator_can_create_classroom_with_homeroom_teacher(): void
    {
        $user = User::factory()->create();
        $teacher = Teacher::create([
            'user_id' => $user->id,
            'gender'  => 'L',
        ]);

        $response = $this->actingAs($this->operator)
                         ->post(route('operator.classrooms.store'), [
                             'academic_year_id'    => $this->academicYear->id,
                             'name'                => 'Kelas 1A',
                             'grade'               => 1,
                             'homeroom_teacher_id' => $teacher->id,
                         ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('classrooms', [
            'name'                => 'Kelas 1A',
            'homeroom_teacher_id' => $teacher->id,
        ]);
    }

    public function test_operator_can_update_classroom(): void
    {
        $classroom = Classroom::create([
            'academic_year_id' => $this->academicYear->id,
            'name'             => 'Kelas 1A',
            'grade'            => 1,
        ]);

        $response = $this->actingAs($this->operator)
                         ->put(route('operator.classrooms.update', $classroom), [
                             'name'  => 'Kelas 1B',
                             'grade' => 1,
                         ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('classrooms', ['name' => 'Kelas 1B']);
    }

    public function test_operator_can_delete_classroom(): void
    {
        $classroom = Classroom::create([
            'academic_year_id' => $this->academicYear->id,
            'name'             => 'Kelas 1A',
            'grade'            => 1,
        ]);

        $response = $this->actingAs($this->operator)
                         ->delete(route('operator.classrooms.destroy', $classroom));

        $response->assertRedirect();
        $this->assertDatabaseMissing('classrooms', ['id' => $classroom->id]);
    }

    public function test_kamad_cannot_create_classroom(): void
    {
        $response = $this->actingAs($this->kamad)
                         ->post(route('operator.classrooms.store'), [
                             'academic_year_id' => $this->academicYear->id,
                             'name'             => 'Kelas 1A',
                             'grade'            => 1,
                         ]);

        $response->assertStatus(403);
    }
}