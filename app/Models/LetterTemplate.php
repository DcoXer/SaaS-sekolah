<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['letter_type_id', 'name', 'content', 'available_placeholders', 'is_active'])]
class LetterTemplate extends Model
{
    protected function casts(): array
    {
        return [
            'available_placeholders' => 'array',
            'is_active'              => 'boolean',
        ];
    }

    public function letterType()
    {
        return $this->belongsTo(LetterType::class);
    }

    public function letters()
    {
        return $this->hasMany(Letter::class);
    }
}