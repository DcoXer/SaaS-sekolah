<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAssessmentComponentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('operator');
    }

    public function rules(): array
    {
        return [
            'name'      => ['required', 'string', 'max:100'],
            'type'      => ['required', 'in:numeric,predicate,narrative'],
            'ki'        => ['nullable', 'in:ki3,ki4'],
            'weight'    => ['required_if:type,numeric', 'integer', 'min:1', 'max:100'],
            'min_score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'max_score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'order'     => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'      => 'Nama komponen wajib diisi.',
            'type.required'      => 'Tipe komponen wajib dipilih.',
            'type.in'            => 'Tipe komponen tidak valid.',
            'weight.required_if' => 'Bobot wajib diisi untuk komponen numerik.',
        ];
    }
}