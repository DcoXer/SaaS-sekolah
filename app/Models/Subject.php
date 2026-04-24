<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'grade'])]
class Subject extends Model
{
    public function teacherSubjects()
    {
        return $this->hasMany(TeacherSubject::class);
    }
}