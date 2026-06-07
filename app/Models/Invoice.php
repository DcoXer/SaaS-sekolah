<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use App\Models\PpdbRegistration;

#[Fillable([
    'student_id', 'ppdb_registration_id', 'payment_type_id', 'academic_year_id',
    'amount', 'status', 'due_date', 'receipt_code'
])]
class Invoice extends Model
{
    protected function casts(): array
    {
        return [
            'due_date' => 'date',
        ];
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function ppdbRegistration()
    {
        return $this->belongsTo(PpdbRegistration::class);
    }

    public function paymentType()
    {
        return $this->belongsTo(PaymentType::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function paymentRequest()
    {
        return $this->hasOne(PaymentRequest::class);
    }

    public function isUnpaid(): bool
    {
        return $this->status === 'unpaid';
    }

    public function isPartial(): bool
    {
        return $this->status === 'partial';
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function getTotalPaidAttribute(): int
    {
        return $this->payments()->sum('amount');
    }

    public function getRemainingAmountAttribute(): int
    {
        return $this->amount - $this->total_paid;
    }
}