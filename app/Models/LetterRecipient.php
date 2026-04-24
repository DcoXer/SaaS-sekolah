<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['letter_id', 'student_id', 'read_at'])]
class LetterRecipient extends Model
{
    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    public function letter()
    {
        return $this->belongsTo(Letter::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function markAsRead(): void
    {
        if (!$this->read_at) {
            $this->update(['read_at' => now()]);
        }
    }
}