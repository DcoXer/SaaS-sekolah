<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GenerateSppRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('tu_keuangan');
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'integer', 'min:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.required' => 'Nominal SPP wajib diisi.',
            'amount.min'      => 'Nominal SPP minimal Rp1.000.',
        ];
    }
}