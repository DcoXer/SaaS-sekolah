<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'invoice_id', 'tu_keuangan_id', 'amount', 'method',
    'midtrans_order_id', 'midtrans_status', 'proof_file',
    'note', 'paid_at'
])]
class Payment extends Model
{
    protected function casts(): array
    {
        return [
            'paid_at' => 'datetime',
        ];
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function tuKeuangan()
    {
        return $this->belongsTo(User::class, 'tu_keuangan_id');
    }

    public function isCash(): bool
    {
        return $this->method === 'cash';
    }

    public function isMidtrans(): bool
    {
        return $this->method === 'midtrans';
    }
}