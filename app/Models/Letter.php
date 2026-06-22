<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'letter_template_id', 'category', 'requested_by', 'student_id',
    'target_grade', 'rejection_note', 'content',
])]
class Letter extends Model
{
    protected function casts(): array
    {
        return [
            'approved_at'  => 'datetime',
            'published_at' => 'datetime',
        ];
    }

    public function letterTemplate()
    {
        return $this->belongsTo(LetterTemplate::class);
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function recipients()
    {
        return $this->hasMany(LetterRecipient::class);
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

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }
}