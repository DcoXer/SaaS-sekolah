<?php

namespace App\Http\Requests;

use App\Services\LetterTemplateService;
use Illuminate\Foundation\Http\FormRequest;

class UpdateLetterTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('operator');
    }

    public function rules(): array
    {
        return [
            'name'                    => ['required', 'string', 'max:100'],
            'content'                 => ['required', 'string'],
            'available_placeholders'  => ['nullable', 'array'],
            'available_placeholders.*'=> ['string', 'in:' . implode(',', array_keys(LetterTemplateService::AVAILABLE_PLACEHOLDERS))],
            'is_active'               => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'    => 'Nama template wajib diisi.',
            'content.required' => 'Konten template wajib diisi.',
        ];
    }
}