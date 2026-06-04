<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['user_id', 'nisn', 'nik', 'nis', 'name', 'gender', 'grade', 'birth_place', 'birth_date', 'address', 'father_name', 'mother_name', 'guardian_name', 'photo', 'status'])]
class Student extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function classrooms()
    {
        return $this->belongsToMany(Classroom::class, 'student_classrooms')
            ->withTimestamps();
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isAlumni(): bool
    {
        return $this->status === 'alumni';
    }
}
