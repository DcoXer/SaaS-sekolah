<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'start_date', 'end_date', 'status'])]
class AcademicYear extends Model
{
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date'   => 'date',
        ];
    }

    public function classrooms()
    {
        return $this->hasMany(Classroom::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function predicateConfigs()
    {
        return $this->hasMany(PredicateConfig::class);
    }

    public function assessmentComponents()
    {
        return $this->hasMany(AssessmentComponent::class);
    }

    public function paymentTypes()
    {
        return $this->hasMany(PaymentType::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
