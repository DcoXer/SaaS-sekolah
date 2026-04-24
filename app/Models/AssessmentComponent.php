<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'academic_year_id', 'subject_id', 'classroom_id',
    'name', 'type', 'ki', 'weight', 'min_score', 'max_score', 'order', 'semester'
])]
class AssessmentComponent extends Model
{
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function studentAssessments()
    {
        return $this->hasMany(StudentAssessment::class);
    }
}