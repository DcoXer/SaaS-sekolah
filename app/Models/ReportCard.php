<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'student_id', 'classroom_id', 'academic_year_id',
    'semester', 'status', 'verify_code', 'approved_at', 'approved_by',
])]
class ReportCard extends Model
{
    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
        ];
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function notes()
    {
        return $this->hasOne(ReportCardNote::class);
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isWaitingApproval(): bool
    {
        return $this->status === 'waiting_approval';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }
}
