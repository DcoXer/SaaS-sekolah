<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Teacher;
use App\Models\TeacherTeachingHour;
use Illuminate\Support\Collection;

class TeachingHourService
{
    public function getAllByYear(AcademicYear $academicYear): Collection
    {
        $hours = TeacherTeachingHour::with('teacher.user')
            ->where('academic_year_id', $academicYear->id)
            ->get()
            ->keyBy('teacher_id');

        $teachers = Teacher::with('user')->get();

        return $teachers->map(function (Teacher $teacher) use ($hours, $academicYear) {
            $hour = $hours->get($teacher->id);
            return [
                'teacher'          => $teacher,
                'teaching_hour'    => $hour,
                'academic_year_id' => $academicYear->id,
            ];
        });
    }

    public function getByTeacherAndYear(Teacher $teacher, AcademicYear $academicYear): ?TeacherTeachingHour
    {
        return TeacherTeachingHour::where('teacher_id', $teacher->id)
            ->where('academic_year_id', $academicYear->id)
            ->first();
    }

    public function set(Teacher $teacher, AcademicYear $academicYear, array $data): TeacherTeachingHour
    {
        return TeacherTeachingHour::updateOrCreate(
            [
                'teacher_id'       => $teacher->id,
                'academic_year_id' => $academicYear->id,
            ],
            [
                'total_hours'          => $data['total_hours'],
                'hourly_rate'          => $data['hourly_rate'],
                'daily_transport_rate' => $data['daily_transport_rate'],
                'position_name'        => $data['position_name'] ?? null,
                'position_allowance'   => $data['position_allowance'] ?? null,
            ]
        );
    }

    public function delete(TeacherTeachingHour $teachingHour): void
    {
        $teachingHour->delete();
    }
}
