<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RequestLetterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('siswa');
    }

    public function rules(): array
    {
        return [
            'letter_template_id' => ['required', 'exists:letter_templates,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'letter_template_id.required' => 'Template surat wajib dipilih.',
            'letter_template_id.exists'   => 'Template surat tidak valid.',
        ];
    }
}