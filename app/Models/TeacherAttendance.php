<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['teacher_id', 'date', 'status', 'notes'])]
class TeacherAttendance extends Model
{
    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function isHadir(): bool
    {
        return $this->status === 'hadir';
    }
}
