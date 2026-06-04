<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'teacher_id', 'academic_year_id', 'period_month', 'period_year',
    'teaching_hours', 'hourly_rate', 'transport_days', 'daily_transport_rate',
    'position_name', 'position_allowance',
    'teaching_hours_amount', 'transport_amount', 'total_amount',
    'status', 'paid_at', 'tu_keuangan_id', 'slip_code',
])]
class TeacherHonorarium extends Model
{
    protected $table = 'teacher_honorariums';

    protected function casts(): array
    {
        return [
            'paid_at' => 'datetime',
        ];
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function tuKeuangan()
    {
        return $this->belongsTo(User::class, 'tu_keuangan_id');
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function periodLabel(): string
    {
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        return ($months[$this->period_month] ?? $this->period_month) . ' ' . $this->period_year;
    }
}
