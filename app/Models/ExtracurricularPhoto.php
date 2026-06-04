<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['extracurricular_id', 'path', 'sort_order'])]
class ExtracurricularPhoto extends Model
{
    public function extracurricular()
    {
        return $this->belongsTo(Extracurricular::class);
    }
}
