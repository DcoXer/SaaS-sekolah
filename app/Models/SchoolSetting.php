<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'name', 'tagline', 'npsn', 'principal_name', 'principal_nip',
    'address', 'phone', 'email', 'website',
    'description', 'vision', 'mission', 'history',
    'logo', 'stamp',
])]
class SchoolSetting extends Model
{
    public static function current(): ?self
    {
        return static::first();
    }
}