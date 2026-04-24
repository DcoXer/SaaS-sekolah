<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RejectLetterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('kamad');
    }

    public function rules(): array
    {
        return [
            'rejection_note' => ['required', 'string', 'min:10'],
        ];
    }

    public function messages(): array
    {
        return [
            'rejection_note.required' => 'Alasan penolakan wajib diisi.',
            'rejection_note.min'      => 'Alasan penolakan minimal 10 karakter.',
        ];
    }
}