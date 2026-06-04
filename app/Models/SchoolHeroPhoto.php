<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['page', 'file_path', 'order'])]
class SchoolHeroPhoto extends Model
{
    public static function forPage(string $page): Collection
    {
        return static::where('page', $page)->orderBy('order')->orderBy('id')->get();
    }
}
