<?php

namespace Tests\Unit\Services;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherSubject;
use App\Models\User;
use App\Services\TeacherSubjectService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherSubjectServiceTest extends TestCase
{
    use RefreshDatabase;

    private TeacherSubjectService $service;
    private AcademicYear $academicYear;
    private Classroom $classroom;
    private Teacher $teacher;
    private Subject $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TeacherSubjectService();

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

        $user = User::create([
            'name'     => 'Budi',
            'email'    => 'budi@test.com',
            'password' => bcrypt('password'),
        ]);

        $this->teacher = Teacher::create([
            'user_id' => $user->id,
            'gender'  => 'L',
        ]);

        $this->subject = Subject::create([
            'name'  => 'Matematika',
            'grade' => 1,
        ]);
    }

    public function test_can_assign_teacher_to_subject(): void
    {
        $assignment = $this->service->assign([
            'teacher_id'       => $this->teacher->id,
            'subject_id'       => $this->subject->id,
            'classroom_id'     => $this->classroom->id,
            'academic_year_id' => $this->academicYear->id,
        ]);

        $this->assertInstanceOf(TeacherSubject::class, $assignment);
        $this->assertDatabaseHas('teacher_subjects', [
            'teacher_id' => $this->teacher->id,
            'subject_id' => $this->subject->id,
        ]);
    }

    public function test_assign_is_idempotent(): void
    {
        $data = [
            'teacher_id'       => $this->teacher->id,
            'subject_id'       => $this->subject->id,
            'classroom_id'     => $this->classroom->id,
            'academic_year_id' => $this->academicYear->id,
        ];

        $this->service->assign($data);
        $this->service->assign($data);

        $this->assertCount(1, TeacherSubject::all());
    }

    public function test_can_unassign_teacher_from_subject(): void
    {
        $assignment = $this->service->assign([
            'teacher_id'       => $this->teacher->id,
            'subject_id'       => $this->subject->id,
            'classroom_id'     => $this->classroom->id,
            'academic_year_id' => $this->academicYear->id,
        ]);

        $this->service->unassign($assignment);

        $this->assertDatabaseMissing('teacher_subjects', ['id' => $assignment->id]);
    }

    public function test_can_sync_classroom_assignments(): void
    {
        $subject2 = Subject::create(['name' => 'IPA', 'grade' => 1]);

        $user2 = User::create([
            'name'     => 'Siti',
            'email'    => 'siti@test.com',
            'password' => bcrypt('password'),
        ]);

        $teacher2 = Teacher::create([
            'user_id' => $user2->id,
            'gender'  => 'P',
        ]);

        // Assign awal
        $this->service->assign([
            'teacher_id'       => $this->teacher->id,
            'subject_id'       => $this->subject->id,
            'classroom_id'     => $this->classroom->id,
            'academic_year_id' => $this->academicYear->id,
        ]);

        // Sync dengan data baru
        $this->service->syncClassroom($this->classroom, $this->academicYear->id, [
            ['teacher_id' => $teacher2->id, 'subject_id' => $subject2->id],
        ]);

        // Assignment lama harus hilang
        $this->assertDatabaseMissing('teacher_subjects', [
            'teacher_id' => $this->teacher->id,
            'subject_id' => $this->subject->id,
        ]);

        // Assignment baru harus ada
        $this->assertDatabaseHas('teacher_subjects', [
            'teacher_id' => $teacher2->id,
            'subject_id' => $subject2->id,
        ]);
    }

    public function test_get_by_classroom_returns_correct_assignments(): void
    {
        $this->service->assign([
            'teacher_id'       => $this->teacher->id,
            'subject_id'       => $this->subject->id,
            'classroom_id'     => $this->classroom->id,
            'academic_year_id' => $this->academicYear->id,
        ]);

        $assignments = $this->service->getByClassroom($this->classroom);

        $this->assertCount(1, $assignments);
        $this->assertEquals($this->classroom->id, $assignments->first()->classroom_id);
    }

    public function test_get_by_teacher_returns_correct_assignments(): void
    {
        $this->service->assign([
            'teacher_id'       => $this->teacher->id,
            'subject_id'       => $this->subject->id,
            'classroom_id'     => $this->classroom->id,
            'academic_year_id' => $this->academicYear->id,
        ]);

        $assignments = $this->service->getByTeacher($this->teacher);

        $this->assertCount(1, $assignments);
        $this->assertEquals($this->teacher->id, $assignments->first()->teacher_id);
    }
}