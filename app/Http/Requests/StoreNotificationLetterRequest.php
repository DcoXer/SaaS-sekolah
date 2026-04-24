<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNotificationLetterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('operator');
    }

    public function rules(): array
    {
        return [
            'letter_template_id' => ['required', 'exists:letter_templates,id'],
            'content'            => ['required', 'string'],
            'target_grade'       => ['nullable', 'integer', 'between:1,6'],
        ];
    }

    public function messages(): array
    {
        return [
            'letter_template_id.required' => 'Template surat wajib dipilih.',
            'content.required'            => 'Konten surat wajib diisi.',
            'target_grade.between'        => 'Kelas target harus antara 1-6.',
        ];
    }
}