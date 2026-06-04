<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'description', 'coach', 'schedule', 'image', 'is_active', 'sort_order'])]
class Extracurricular extends Model
{
    protected function casts(): array
    {
        return [
            'is_active'  => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function achievements()
    {
        return $this->hasMany(ExtracurricularAchievement::class)->orderBy('year', 'desc')->orderBy('sort_order');
    }

    public function photos()
    {
        return $this->hasMany(ExtracurricularPhoto::class)->orderBy('sort_order')->orderBy('id');
    }
}
