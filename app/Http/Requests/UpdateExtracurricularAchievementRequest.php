<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateExtracurricularAchievementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('operator');
    }

    public function rules(): array
    {
        return [
            'title'      => ['required', 'string', 'max:200'],
            'year'       => ['required', 'integer', 'min:1900', 'max:2100'],
            'level'      => ['required', 'string', Rule::in(['kecamatan', 'kabupaten', 'kota', 'provinsi', 'nasional', 'internasional'])],
            'rank'       => ['required', 'string', 'max:100'],
            'sort_order' => ['integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Judul prestasi wajib diisi.',
            'year.required'  => 'Tahun wajib diisi.',
            'level.required' => 'Tingkat wajib dipilih.',
            'rank.required'  => 'Juara/peringkat wajib diisi.',
        ];
    }
}
