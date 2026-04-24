<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'title', 'description', 'requirements',
    'registration_start', 'registration_end', 'announcement_date',
    'quota', 'is_open',
])]
class PpdbSetting extends Model
{
    protected function casts(): array
    {
        return [
            'registration_start'  => 'date',
            'registration_end'    => 'date',
            'announcement_date'   => 'date',
            'is_open'             => 'boolean',
            'quota'               => 'integer',
        ];
    }

    public function registrations()
    {
        return $this->hasMany(PpdbRegistration::class);
    }

    public function isRegistrationOpen(): bool
    {
        if (! $this->is_open) return false;
        $now = now()->toDateString();
        return $now >= $this->registration_start->toDateString()
            && $now <= $this->registration_end->toDateString();
    }
}
