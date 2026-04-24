<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'student_id', 'assessment_component_id', 'academic_year_id',
    'classroom_id', 'input_by', 'semester', 'score', 'predicate', 'narrative'
])]
class StudentAssessment extends Model
{
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function assessmentComponent()
    {
        return $this->belongsTo(AssessmentComponent::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function inputBy()
    {
        return $this->belongsTo(User::class, 'input_by');
    }
}