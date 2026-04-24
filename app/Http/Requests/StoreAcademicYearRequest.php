<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAcademicYearRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('operator');
    }

    public function rules(): array
    {
        return [
            'name'       => ['required', 'string', 'max:20', 'unique:academic_years,name'],
            'start_date' => ['required', 'date'],
            'end_date'   => ['required', 'date', 'after:start_date'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'       => 'Nama tahun ajaran wajib diisi.',
            'name.unique'         => 'Tahun ajaran sudah ada.',
            'start_date.required' => 'Tanggal mulai wajib diisi.',
            'end_date.after'      => 'Tanggal selesai harus setelah tanggal mulai.',
        ];
    }
}