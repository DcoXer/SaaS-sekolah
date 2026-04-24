<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'academic_year_id', 'name', 'cycle', 'amount',
    'due_date', 'grade', 'is_exam_related', 'is_active'
])]
class PaymentType extends Model
{
    protected function casts(): array
    {
        return [
            'due_date'        => 'date',
            'is_exam_related' => 'boolean',
            'is_active'       => 'boolean',
        ];
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function isMonthly(): bool
    {
        return $this->cycle === 'monthly';
    }

    public function isYearly(): bool
    {
        return $this->cycle === 'yearly';
    }

    public function isOnce(): bool
    {
        return $this->cycle === 'once';
    }
}