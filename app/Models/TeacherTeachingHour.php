<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['teacher_id', 'academic_year_id', 'total_hours', 'hourly_rate', 'daily_transport_rate', 'position_name', 'position_allowance'])]
class TeacherTeachingHour extends Model
{
    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function monthlyTeachingAmount(): int
    {
        return $this->total_hours * $this->hourly_rate;
    }
}
