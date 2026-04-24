<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['user_id', 'nip', 'type', 'position', 'gender', 'phone', 'photo'])]
class Teacher extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function homeroomClassrooms()
    {
        return $this->hasMany(Classroom::class, 'homeroom_teacher_id');
    }

    public function teacherSubjects()
    {
        return $this->hasMany(TeacherSubject::class);
    }

    public function teachingHours()
    {
        return $this->hasMany(TeacherTeachingHour::class);
    }

    public function attendances()
    {
        return $this->hasMany(TeacherAttendance::class);
    }

    public function honorariums()
    {
        return $this->hasMany(TeacherHonorarium::class);
    }

    public function isGuruKelas(): bool
    {
        return $this->type === 'guru_kelas';
    }

    public function isGuruBidang(): bool
    {
        return $this->type === 'guru_bidang';
    }
}