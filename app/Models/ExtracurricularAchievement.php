<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['extracurricular_id', 'title', 'year', 'level', 'rank', 'sort_order'])]
class ExtracurricularAchievement extends Model
{
    public function extracurricular()
    {
        return $this->belongsTo(Extracurricular::class);
    }
}
