<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SyncPredicateConfigRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('operator');
    }

    public function rules(): array
    {
        return [
            'configs'              => ['required', 'array', 'min:1'],
            'configs.*.min_score'  => ['required', 'integer', 'min:0', 'max:100'],
            'configs.*.max_score'  => ['required', 'integer', 'min:0', 'max:100'],
            'configs.*.predicate'  => ['required', 'string', 'max:5'],
        ];
    }

    public function messages(): array
    {
        return [
            'configs.required'             => 'Konfigurasi predikat wajib diisi.',
            'configs.*.min_score.required' => 'Nilai minimum wajib diisi.',
            'configs.*.max_score.required' => 'Nilai maksimum wajib diisi.',
            'configs.*.predicate.required' => 'Predikat wajib diisi.',
        ];
    }
}