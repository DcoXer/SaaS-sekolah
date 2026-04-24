<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\StudentClassroom;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherSubject;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ClassroomService
{
    public function getAll(): Collection
    {
        return Classroom::with(['academicYear', 'homeroomTeacher.user'])
                        ->latest()
                        ->get();
    }

    public function getByAcademicYear(AcademicYear $academicYear): Collection
    {
        return Classroom::with(['homeroomTeacher.user'])
                        ->where('academic_year_id', $academicYear->id)
                        ->orderBy('grade')
                        ->get();
    }

    public function getById(Classroom $classroom): Classroom
    {
        return $classroom->load([
            'academicYear',
            'homeroomTeacher.user',
            'students',
            'teacherSubjects.teacher.user',
            'teacherSubjects.subject',
        ]);
    }

    public function create(array $data): Classroom
    {
        return Classroom::create([
            'academic_year_id'    => $data['academic_year_id'],
            'name'                => $data['name'],
            'grade'               => $data['grade'],
            'homeroom_teacher_id' => $data['homeroom_teacher_id'] ?? null,
        ]);
    }

    public function update(Classroom $classroom, array $data): Classroom
    {
        $classroom->update([
            'name'  => $data['name'],
            'grade' => $data['grade'],
        ]);

        return $classroom->fresh(['academicYear', 'homeroomTeacher.user']);
    }

    public function delete(Classroom $classroom): void
    {
        $classroom->delete();
    }

    public function getAvailableStudents(Classroom $classroom, AcademicYear $academicYear): Collection
    {
        return Student::where('status', 'active')
                      ->where('grade', $classroom->grade)
                      ->whereNotIn('id', function ($query) use ($academicYear) {
                          $query->select('student_id')
                                ->from('student_classrooms')
                                ->where('academic_year_id', $academicYear->id);
                      })
                      ->orderBy('name')
                      ->get();
    }

    public function assignStudents(Classroom $classroom, AcademicYear $academicYear, array $studentIds): void
    {
        DB::transaction(function () use ($classroom, $academicYear, $studentIds) {
            foreach ($studentIds as $studentId) {
                // Double check siswa belum ada di rombel lain tahun ajaran ini
                $alreadyAssigned = StudentClassroom::where('student_id', $studentId)
                                                   ->where('academic_year_id', $academicYear->id)
                                                   ->exists();

                if (!$alreadyAssigned) {
                    StudentClassroom::create([
                        'student_id'       => $studentId,
                        'classroom_id'     => $classroom->id,
                        'academic_year_id' => $academicYear->id,
                    ]);
                }
            }
        });
    }

    public function assignGuruKelas(Classroom $classroom, Teacher $teacher, AcademicYear $academicYear): void
    {
        abort_if($classroom->grade > 3, 422, 'Guru kelas hanya bisa di-assign ke kelas 1-3.');
        abort_if(!$teacher->isGuruKelas(), 422, 'Guru ini bukan guru kelas.');

        $alreadyAssigned = Classroom::where('homeroom_teacher_id', $teacher->id)
                                    ->where('academic_year_id', $academicYear->id)
                                    ->where('id', '!=', $classroom->id)
                                    ->exists();

        abort_if($alreadyAssigned, 422, 'Guru kelas ini sudah di-assign ke kelas lain.');

        $classroom->update(['homeroom_teacher_id' => $teacher->id]);

        $subjects = Subject::where('grade', $classroom->grade)->get();

        foreach ($subjects as $subject) {
            TeacherSubject::firstOrCreate([
                'teacher_id'       => $teacher->id,
                'subject_id'       => $subject->id,
                'classroom_id'     => $classroom->id,
                'academic_year_id' => $academicYear->id,
            ]);
        }
    }

    public function assignWaliKelas(Classroom $classroom, Teacher $teacher, AcademicYear $academicYear): void
    {
        abort_if($classroom->grade < 4, 422, 'Wali kelas 4-6 hanya untuk kelas tingkat 4-6.');
        abort_if(!$teacher->isGuruBidang(), 422, 'Wali kelas harus dari guru bidang.');

        $alreadyWaliKelas = Classroom::where('homeroom_teacher_id', $teacher->id)
                                     ->where('academic_year_id', $academicYear->id)
                                     ->whereBetween('grade', [4, 6])
                                     ->where('id', '!=', $classroom->id)
                                     ->exists();

        abort_if($alreadyWaliKelas, 422, 'Guru ini sudah menjadi wali kelas di rombel lain.');

        $classroom->update(['homeroom_teacher_id' => $teacher->id]);
    }

    public function assignGuruBidang(Classroom $classroom, Teacher $teacher, int $subjectId, AcademicYear $academicYear): void
    {
        abort_if($classroom->grade < 4, 422, 'Guru bidang hanya untuk kelas 4-6.');
        abort_if(!$teacher->isGuruBidang(), 422, 'Guru ini bukan guru bidang.');

        TeacherSubject::firstOrCreate([
            'teacher_id'       => $teacher->id,
            'subject_id'       => $subjectId,
            'classroom_id'     => $classroom->id,
            'academic_year_id' => $academicYear->id,
        ]);
    }

    public function removeAllAssignments(Classroom $classroom, AcademicYear $academicYear): void
    {
        TeacherSubject::where('classroom_id', $classroom->id)
                       ->where('academic_year_id', $academicYear->id)
                       ->delete();

        $classroom->update(['homeroom_teacher_id' => null]);
    }

    public function getAvailableGuruKelas(AcademicYear $academicYear): Collection
    {
        $assignedIds = Classroom::where('academic_year_id', $academicYear->id)
                                 ->whereBetween('grade', [1, 3])
                                 ->whereNotNull('homeroom_teacher_id')
                                 ->pluck('homeroom_teacher_id');

        return Teacher::where('type', 'guru_kelas')
                      ->whereNotIn('id', $assignedIds)
                      ->with('user')
                      ->get();
    }

    public function getAvailableWaliKelas(Classroom $classroom, AcademicYear $academicYear): Collection
    {
        $assignedIds = Classroom::where('academic_year_id', $academicYear->id)
                                 ->whereBetween('grade', [4, 6])
                                 ->whereNotNull('homeroom_teacher_id')
                                 ->where('id', '!=', $classroom->id)
                                 ->pluck('homeroom_teacher_id');

        return Teacher::where('type', 'guru_bidang')
                      ->whereNotIn('id', $assignedIds)
                      ->with('user')
                      ->get();
    }
}