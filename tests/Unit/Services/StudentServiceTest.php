<?php

namespace Tests\Unit\Services;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\Teacher;
use App\Services\StudentService;
use App\Services\TeacherService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentServiceTest extends TestCase
{
    use RefreshDatabase;

    private StudentService $service;
    private AcademicYear $academicYear;
    private Classroom $classroom;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new StudentService();

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

    public function test_can_create_student(): void
    {
        $student = $this->service->create([
            'nis'    => '001',
            'name'   => 'Ahmad',
            'gender' => 'L',
        ]);

        $this->assertInstanceOf(Student::class, $student);
        $this->assertDatabaseHas('students', ['nis' => '001']);
    }

    public function test_can_create_student_with_wali_account(): void
    {
        $student = $this->service->create([
            'nis'         => '001',
            'name'        => 'Ahmad',
            'gender'      => 'L',
            'parent_name' => 'Bapak Ahmad',
            'email'       => 'wali@test.com',
            'password'    => 'password',
        ]);

        $this->assertNotNull($student->user);
        $this->assertTrue($student->user->hasRole('siswa'));
    }

    public function test_can_assign_student_to_classroom(): void
    {
        $student = $this->service->create([
            'nis'    => '001',
            'name'   => 'Ahmad',
            'gender' => 'L',
        ]);

        $this->service->assignToClassroom(
            $student,
            $this->classroom->id,
            $this->academicYear->id
        );

        $this->assertDatabaseHas('student_classrooms', [
            'student_id'       => $student->id,
            'classroom_id'     => $this->classroom->id,
            'academic_year_id' => $this->academicYear->id,
        ]);
    }

    public function test_assign_classroom_updates_existing_assignment(): void
    {
        $student = $this->service->create([
            'nis'    => '001',
            'name'   => 'Ahmad',
            'gender' => 'L',
        ]);

        $classroom2 = Classroom::create([
            'academic_year_id' => $this->academicYear->id,
            'name'             => 'Kelas 1B',
            'grade'            => 1,
        ]);

        // Assign pertama
        $this->service->assignToClassroom($student, $this->classroom->id, $this->academicYear->id);

        // Assign ke kelas lain di tahun ajaran yang sama
        $this->service->assignToClassroom($student, $classroom2->id, $this->academicYear->id);

        // Harus cuma ada satu record di tahun ajaran ini
        $this->assertCount(1, $student->classrooms()->wherePivot('academic_year_id', $this->academicYear->id)->get());
    }

    public function test_delete_student_also_deletes_user(): void
    {
        $student = $this->service->create([
            'nis'         => '001',
            'name'        => 'Ahmad',
            'gender'      => 'L',
            'parent_name' => 'Bapak Ahmad',
            'email'       => 'wali@test.com',
            'password'    => 'password',
        ]);

        $userId = $student->user_id;
        $this->service->delete($student);

        $this->assertDatabaseMissing('students', ['id' => $student->id]);
        $this->assertDatabaseMissing('users', ['id' => $userId]);
    }
}