<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['report_card_id', 'homeroom_notes', 'principal_notes'])]
class ReportCardNote extends Model
{
    public function reportCard()
    {
        return $this->belongsTo(ReportCard::class);
    }
}