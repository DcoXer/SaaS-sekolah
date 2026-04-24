<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'category', 'description', 'is_active'])]
class LetterType extends Model
{
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function templates()
    {
        return $this->hasMany(LetterTemplate::class);
    }

    public function isKeterangan(): bool
    {
        return $this->category === 'keterangan';
    }

    public function isPemberitahuan(): bool
    {
        return $this->category === 'pemberitahuan';
    }
}