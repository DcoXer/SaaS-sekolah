<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['academic_year_id', 'min_score', 'max_score', 'predicate'])]
class PredicateConfig extends Model
{
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }
}