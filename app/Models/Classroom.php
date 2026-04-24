<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['academic_year_id', 'name', 'grade', 'homeroom_teacher_id'])]
class Classroom extends Model
{
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function homeroomTeacher()
    {
        return $this->belongsTo(Teacher::class, 'homeroom_teacher_id');
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'student_classrooms')
                    ->withTimestamps();
    }

    public function teacherSubjects()
    {
        return $this->hasMany(TeacherSubject::class);
    }
}