<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['invoice_id', 'student_id', 'status'])]
class PaymentRequest extends Model
{
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
