<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAssessmentComponentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('operator');
    }

    public function rules(): array
    {
        return [
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'subject_id'       => ['required', 'exists:subjects,id'],
            'classroom_id'     => ['required', 'exists:classrooms,id'],
            'name'             => ['required', 'string', 'max:100'],
            'type'             => ['required', 'in:numeric,predicate,narrative'],
            'ki'               => ['nullable', 'in:ki3,ki4'],
            'weight'           => ['required_if:type,numeric', 'integer', 'min:1', 'max:100'],
            'min_score'        => ['nullable', 'integer', 'min:0', 'max:100'],
            'max_score'        => ['nullable', 'integer', 'min:0', 'max:100'],
            'order'            => ['nullable', 'integer', 'min:0'],
            'semester'         => ['required', 'in:1,2'],
        ];
    }

    public function messages(): array
    {
        return [
            'academic_year_id.required' => 'Tahun ajaran wajib dipilih.',
            'subject_id.required'       => 'Mata pelajaran wajib dipilih.',
            'classroom_id.required'     => 'Kelas wajib dipilih.',
            'name.required'             => 'Nama komponen wajib diisi.',
            'type.required'             => 'Tipe komponen wajib dipilih.',
            'type.in'                   => 'Tipe komponen tidak valid.',
            'weight.required_if'        => 'Bobot wajib diisi untuk komponen numerik.',
            'semester.required'         => 'Semester wajib dipilih.',
            'semester.in'               => 'Semester tidak valid.',
        ];
    }
}