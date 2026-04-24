<?php

namespace Tests\Unit\Services;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\StudentClassroom;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherSubject;
use App\Models\User;
use App\Services\ClassroomService;
use App\Services\TeacherService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class ClassroomServiceTest extends TestCase
{
    use RefreshDatabase;

    private ClassroomService $service;
    private AcademicYear $academicYear;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ClassroomService();

        $this->academicYear = AcademicYear::create([
            'name'       => '2024/2025',
            'start_date' => '2024-07-15',
            'end_date'   => '2025-06-30',
            'status'     => 'active',
        ]);
    }

    public function test_can_create_classroom(): void
    {
        $classroom = $this->service->create([
            'academic_year_id' => $this->academicYear->id,
            'name'             => 'Kelas 1A',
            'grade'            => 1,
        ]);

        $this->assertInstanceOf(Classroom::class, $classroom);
        $this->assertDatabaseHas('classrooms', [
            'name'  => 'Kelas 1A',
            'grade' => 1,
        ]);
    }

    public function test_can_create_classroom_with_homeroom_teacher(): void
    {
        $user = User::create([
            'name'     => 'Budi Santoso',
            'email'    => 'budi@sekolah.test',
            'password' => bcrypt('password'),
        ]);

        $teacher = Teacher::create([
            'user_id' => $user->id,
            'gender'  => 'L',
        ]);

        $classroom = $this->service->create([
            'academic_year_id'    => $this->academicYear->id,
            'name'                => 'Kelas 1A',
            'grade'               => 1,
            'homeroom_teacher_id' => $teacher->id,
        ]);

        $this->assertEquals($teacher->id, $classroom->homeroom_teacher_id);
        $this->assertNotNull($classroom->homeroomTeacher);
    }

    public function test_can_update_classroom(): void
    {
        $classroom = $this->service->create([
            'academic_year_id' => $this->academicYear->id,
            'name'             => 'Kelas 1A',
            'grade'            => 1,
        ]);

        $updated = $this->service->update($classroom, [
            'name'  => 'Kelas 1B',
            'grade' => 1,
        ]);

        $this->assertEquals('Kelas 1B', $updated->name);
    }

    public function test_can_delete_classroom(): void
    {
        $classroom = $this->service->create([
            'academic_year_id' => $this->academicYear->id,
            'name'             => 'Kelas 1A',
            'grade'            => 1,
        ]);

        $this->service->delete($classroom);

        $this->assertDatabaseMissing('classrooms', ['id' => $classroom->id]);
    }

    public function test_get_available_students_filters_by_grade(): void
    {
        $classroom = $this->service->create([
            'academic_year_id' => $this->academicYear->id,
            'name'             => 'Kelas 1A',
            'grade'            => 1,
        ]);

        // Siswa grade 1
        Student::create([
            'nis'    => 'NIS001',
            'name'   => 'Siswa Grade 1',
            'gender' => 'L',
            'grade'  => 1,
            'status' => 'active',
        ]);

        // Siswa grade 2
        Student::create([
            'nis'    => 'NIS002',
            'name'   => 'Siswa Grade 2',
            'gender' => 'L',
            'grade'  => 2,
            'status' => 'active',
        ]);

        $available = $this->service->getAvailableStudents($classroom, $this->academicYear);

        $this->assertCount(1, $available);
        $this->assertEquals('NIS001', $available->first()->nis);
    }

    public function test_get_available_students_excludes_already_assigned(): void
    {
        $classroom = $this->service->create([
            'academic_year_id' => $this->academicYear->id,
            'name'             => 'Kelas 1A',
            'grade'            => 1,
        ]);

        $student = Student::create([
            'nis'    => 'NIS001',
            'name'   => 'Siswa Grade 1',
            'gender' => 'L',
            'grade'  => 1,
            'status' => 'active',
        ]);

        // Assign siswa ke rombel
        StudentClassroom::create([
            'student_id'       => $student->id,
            'classroom_id'     => $classroom->id,
            'academic_year_id' => $this->academicYear->id,
        ]);

        $available = $this->service->getAvailableStudents($classroom, $this->academicYear);

        $this->assertCount(0, $available);
    }

    public function test_assign_guru_kelas_to_grade_1_3(): void
    {
        $classroom = $this->service->create([
            'academic_year_id' => $this->academicYear->id,
            'name'             => 'Kelas 1A',
            'grade'            => 1,
        ]);

        $subject = Subject::create(['name' => 'Matematika', 'grade' => 1]);

        $user = User::create([
            'name'     => 'Guru Kelas',
            'email'    => 'gurukelas@sekolah.test',
            'password' => bcrypt('password'),
        ]);

        $teacher = Teacher::create([
            'user_id' => $user->id,
            'type'    => 'guru_kelas',
            'gender'  => 'L',
        ]);

        $this->service->assignGuruKelas($classroom, $teacher, $this->academicYear);

        $this->assertEquals($teacher->id, $classroom->fresh()->homeroom_teacher_id);
        $this->assertDatabaseHas('teacher_subjects', [
            'teacher_id'       => $teacher->id,
            'subject_id'       => $subject->id,
            'classroom_id'     => $classroom->id,
            'academic_year_id' => $this->academicYear->id,
        ]);
    }

    public function test_guru_kelas_cannot_be_assigned_to_two_classes(): void
    {
        $classroom1 = $this->service->create([
            'academic_year_id' => $this->academicYear->id,
            'name'             => 'Kelas 1A',
            'grade'            => 1,
        ]);

        $classroom2 = $this->service->create([
            'academic_year_id' => $this->academicYear->id,
            'name'             => 'Kelas 1B',
            'grade'            => 1,
        ]);

        $user = User::create([
            'name'     => 'Guru Kelas',
            'email'    => 'gurukelas@sekolah.test',
            'password' => bcrypt('password'),
        ]);

        $teacher = Teacher::create([
            'user_id' => $user->id,
            'type'    => 'guru_kelas',
            'gender'  => 'L',
        ]);

        $this->service->assignGuruKelas($classroom1, $teacher, $this->academicYear);

        $this->expectException(HttpException::class);
        $this->service->assignGuruKelas($classroom2, $teacher, $this->academicYear);
    }

    public function test_assign_wali_kelas_to_grade_4_6(): void
    {
        $classroom = $this->service->create([
            'academic_year_id' => $this->academicYear->id,
            'name'             => 'Kelas 4A',
            'grade'            => 4,
        ]);

        $user = User::create([
            'name'     => 'Guru Bidang',
            'email'    => 'gurubidang@sekolah.test',
            'password' => bcrypt('password'),
        ]);

        $teacher = Teacher::create([
            'user_id' => $user->id,
            'type'    => 'guru_bidang',
            'gender'  => 'L',
        ]);

        $this->service->assignWaliKelas($classroom, $teacher, $this->academicYear);

        $this->assertEquals($teacher->id, $classroom->fresh()->homeroom_teacher_id);
    }

    public function test_guru_bidang_cannot_be_wali_kelas_twice(): void
    {
        $classroom1 = $this->service->create([
            'academic_year_id' => $this->academicYear->id,
            'name'             => 'Kelas 4A',
            'grade'            => 4,
        ]);

        $classroom2 = $this->service->create([
            'academic_year_id' => $this->academicYear->id,
            'name'             => 'Kelas 4B',
            'grade'            => 4,
        ]);

        $user = User::create([
            'name'     => 'Guru Bidang',
            'email'    => 'gurubidang@sekolah.test',
            'password' => bcrypt('password'),
        ]);

        $teacher = Teacher::create([
            'user_id' => $user->id,
            'type'    => 'guru_bidang',
            'gender'  => 'L',
        ]);

        $this->service->assignWaliKelas($classroom1, $teacher, $this->academicYear);

        $this->expectException(HttpException::class);
        $this->service->assignWaliKelas($classroom2, $teacher, $this->academicYear);
    }

    public function test_guru_bidang_can_teach_different_subjects_in_different_classes(): void
    {
        $classroom4 = $this->service->create([
            'academic_year_id' => $this->academicYear->id,
            'name'             => 'Kelas 4A',
            'grade'            => 4,
        ]);

        $classroom5 = $this->service->create([
            'academic_year_id' => $this->academicYear->id,
            'name'             => 'Kelas 5A',
            'grade'            => 5,
        ]);

        $subject4 = Subject::create(['name' => 'Matematika', 'grade' => 4]);
        $subject5 = Subject::create(['name' => 'IPAS', 'grade' => 5]);

        $user = User::create([
            'name'     => 'Guru Bidang',
            'email'    => 'gurubidang@sekolah.test',
            'password' => bcrypt('password'),
        ]);

        $teacher = Teacher::create([
            'user_id' => $user->id,
            'type'    => 'guru_bidang',
            'gender'  => 'L',
        ]);

        $this->service->assignGuruBidang($classroom4, $teacher, $subject4->id, $this->academicYear);
        $this->service->assignGuruBidang($classroom5, $teacher, $subject5->id, $this->academicYear);

        $this->assertDatabaseHas('teacher_subjects', [
            'teacher_id'   => $teacher->id,
            'subject_id'   => $subject4->id,
            'classroom_id' => $classroom4->id,
        ]);

        $this->assertDatabaseHas('teacher_subjects', [
            'teacher_id'   => $teacher->id,
            'subject_id'   => $subject5->id,
            'classroom_id' => $classroom5->id,
        ]);
    }

    public function test_get_by_academic_year_returns_correct_classrooms(): void
    {
        $this->service->create([
            'academic_year_id' => $this->academicYear->id,
            'name'             => 'Kelas 1A',
            'grade'            => 1,
        ]);

        $this->service->create([
            'academic_year_id' => $this->academicYear->id,
            'name'             => 'Kelas 2A',
            'grade'            => 2,
        ]);

        $otherYear = AcademicYear::create([
            'name'       => '2025/2026',
            'start_date' => '2025-07-15',
            'end_date'   => '2026-06-30',
            'status'     => 'pending',
        ]);

        $this->service->create([
            'academic_year_id' => $otherYear->id,
            'name'             => 'Kelas 1A',
            'grade'            => 1,
        ]);

        $classrooms = $this->service->getByAcademicYear($this->academicYear);

        $this->assertCount(2, $classrooms);
        $classrooms->each(fn($c) => $this->assertEquals($this->academicYear->id, $c->academic_year_id));
    }
}