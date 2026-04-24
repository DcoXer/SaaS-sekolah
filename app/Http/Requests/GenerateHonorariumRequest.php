<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GenerateHonorariumRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('tu_keuangan');
    }

    public function rules(): array
    {
        return [
            'teacher_id'       => ['required', 'exists:teachers,id'],
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'period_month'     => ['required', 'integer', 'between:1,12'],
            'period_year'      => ['required', 'integer', 'min:2000', 'max:2100'],
        ];
    }

    public function messages(): array
    {
        return [
            'teacher_id.required'       => 'Guru wajib dipilih.',
            'teacher_id.exists'         => 'Guru tidak valid.',
            'academic_year_id.required' => 'Tahun ajaran wajib dipilih.',
            'academic_year_id.exists'   => 'Tahun ajaran tidak valid.',
            'period_month.required'     => 'Bulan wajib dipilih.',
            'period_month.between'      => 'Bulan tidak valid.',
            'period_year.required'      => 'Tahun wajib diisi.',
        ];
    }
}
