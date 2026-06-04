<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'name', 'tagline', 'npsn', 'principal_name', 'principal_nip',
    'address', 'phone', 'email', 'website',
    'latitude', 'longitude', 'attendance_radius',
    'description', 'vision', 'mission', 'history',
    'logo', 'stamp',
    'hero_welcome', 'hero_tentang', 'hero_galeri', 'hero_ekskul',
])]
class SchoolSetting extends Model
{
    public static function current(): ?self
    {
        return static::first();
    }
}