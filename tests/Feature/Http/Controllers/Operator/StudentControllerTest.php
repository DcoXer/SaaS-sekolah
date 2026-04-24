<?php

namespace Tests\Feature\Http\Controllers\Operator;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $operator;
    private User $kamad;
    private AcademicYear $academicYear;
    private Classroom $classroom;

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
    }

    public function test_operator_can_view_students(): void
    {
        $response = $this->actingAs($this->operator)
                         ->get(route('operator.students.index'));

        $response->assertStatus(200);
    }

    public function test_guest_cannot_view_students(): void
    {
        $response = $this->get(route('operator.students.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_operator_can_create_student(): void
    {
        $response = $this->actingAs($this->operator)
                         ->post(route('operator.students.store'), [
                             'nis'    => '001',
                             'name'   => 'Ahmad',
                             'gender' => 'L',
                             'grade'  => 1,
                         ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('students', ['nis' => '001']);
    }

    public function test_operator_can_create_student_with_wali_account(): void
    {
        $response = $this->actingAs($this->operator)
                         ->post(route('operator.students.store'), [
                             'nis'         => '001',
                             'name'        => 'Ahmad',
                             'gender'      => 'L',
                             'grade'       => 1,
                             'parent_name' => 'Bapak Ahmad',
                             'email'       => 'wali@test.com',
                             'password'    => 'password123',
                         ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', ['email' => 'wali@test.com']);
    }

    public function test_store_fails_with_duplicate_nis(): void
    {
        Student::create([
            'nis'    => '001',
            'name'   => 'Ahmad',
            'gender' => 'L',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->operator)
                         ->post(route('operator.students.store'), [
                             'nis'    => '001',
                             'name'   => 'Budi',
                             'gender' => 'L',
                         ]);

        $response->assertSessionHasErrors('nis');
    }

    public function test_operator_can_update_student(): void
    {
        $student = Student::create([
            'nis'    => '001',
            'name'   => 'Ahmad',
            'gender' => 'L',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->operator)
                         ->put(route('operator.students.update', $student), [
                             'nis'    => '001',
                             'name'   => 'Ahmad Updated',
                             'gender' => 'L',
                             'grade'  => 1,
                         ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('students', ['name' => 'Ahmad Updated']);
    }

    public function test_operator_can_assign_student_to_classroom(): void
    {
        $student = Student::create([
            'nis'    => '001',
            'name'   => 'Ahmad',
            'gender' => 'L',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->operator)
                         ->patch(route('operator.students.assign-classroom', $student), [
                             'classroom_id'     => $this->classroom->id,
                             'academic_year_id' => $this->academicYear->id,
                         ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('student_classrooms', [
            'student_id'   => $student->id,
            'classroom_id' => $this->classroom->id,
        ]);
    }

    public function test_operator_can_delete_student(): void
    {
        $student = Student::create([
            'nis'    => '001',
            'name'   => 'Ahmad',
            'gender' => 'L',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->operator)
                         ->delete(route('operator.students.destroy', $student));

        $response->assertRedirect();
        $this->assertDatabaseMissing('students', ['id' => $student->id]);
    }

    public function test_kamad_cannot_create_student(): void
    {
        $response = $this->actingAs($this->kamad)
                         ->post(route('operator.students.store'), [
                             'nis'    => '001',
                             'name'   => 'Ahmad',
                             'gender' => 'L',
                         ]);

        $response->assertStatus(403);
    }
}