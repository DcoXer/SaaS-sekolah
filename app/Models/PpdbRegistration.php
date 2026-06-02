<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'ppdb_setting_id', 'registration_number',
    'full_name', 'nik_siswa', 'no_kk', 'birth_place', 'birth_date', 'gender', 'religion',
    'address', 'province', 'regency', 'district', 'village',
    'parent_name', 'parent_phone', 'parent_email', 'previous_school',
    'father_name', 'father_nik', 'father_phone',
    'mother_name', 'mother_nik', 'mother_phone',
    'photo', 'document_kk', 'document_akta',
    'status', 'notes', 'reviewed_at', 'reviewed_by',
])]
class PpdbRegistration extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'birth_date'    => 'date',
            'reviewed_at'   => 'datetime',
        ];
    }

    public function ppdbSetting()
    {
        return $this->belongsTo(PpdbSetting::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
