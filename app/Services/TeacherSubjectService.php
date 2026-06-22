<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Teacher;
use App\Models\TeacherSubject;
use Illuminate\Database\Eloquent\Collection;

class TeacherSubjectService
{
    public function getByClassroom(Classroom $classroom): Collection
    {
        return TeacherSubject::with(['teacher.user', 'subject'])
                             ->where('classroom_id', $classroom->id)
                             ->get();
    }

    public function getByTeacher(Teacher $teacher): Collection
    {
        $activeYearId = AcademicYear::where('status', 'active')->value('id');

        return TeacherSubject::with(['subject', 'classroom', 'academicYear'])
                             ->where('teacher_id', $teacher->id)
                             ->where('academic_year_id', $activeYearId)
                             ->get();
    }

    public function getByAcademicYear(AcademicYear $academicYear): Collection
    {
        return TeacherSubject::with(['teacher.user', 'subject', 'classroom'])
                             ->where('academic_year_id', $academicYear->id)
                             ->get();
    }

    public function assign(array $data): TeacherSubject
    {
        // Unique key sekarang: (subject_id, classroom_id, academic_year_id)
        $ts = TeacherSubject::where('subject_id', $data['subject_id'])
            ->where('classroom_id', $data['classroom_id'])
            ->where('academic_year_id', $data['academic_year_id'])
            ->first();

        if ($ts) {
            $ts->update(['teacher_id' => $data['teacher_id']]);
            return $ts;
        }

        return TeacherSubject::create([
            'teacher_id'       => $data['teacher_id'],
            'subject_id'       => $data['subject_id'],
            'classroom_id'     => $data['classroom_id'],
            'academic_year_id' => $data['academic_year_id'],
        ]);
    }

    public function unassign(TeacherSubject $teacherSubject): void
    {
        $teacherSubject->delete();
    }

    public function syncClassroom(Classroom $classroom, int $academicYearId, array $assignments): void
    {
        // Hapus semua assignment di kelas + tahun ajaran ini
        TeacherSubject::where('classroom_id', $classroom->id)
                      ->where('academic_year_id', $academicYearId)
                      ->delete();

        // Insert yang baru
        foreach ($assignments as $assignment) {
            TeacherSubject::create([
                'teacher_id'       => $assignment['teacher_id'],
                'subject_id'       => $assignment['subject_id'],
                'classroom_id'     => $classroom->id,
                'academic_year_id' => $academicYearId,
            ]);
        }
    }
}