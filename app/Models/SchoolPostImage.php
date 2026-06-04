<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['school_post_id', 'path', 'sort_order'])]
class SchoolPostImage extends Model
{
    public function post(): BelongsTo
    {
        return $this->belongsTo(SchoolPost::class, 'school_post_id');
    }
}
