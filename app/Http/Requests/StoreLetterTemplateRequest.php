<?php

namespace App\Http\Requests;

use App\Services\LetterTemplateService;
use Illuminate\Foundation\Http\FormRequest;

class StoreLetterTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('operator');
    }

    public function rules(): array
    {
        return [
            'letter_type_id'          => ['required', 'exists:letter_types,id'],
            'name'                    => ['required', 'string', 'max:100'],
            'content'                 => ['required', 'string'],
            'available_placeholders'  => ['nullable', 'array'],
            'available_placeholders.*'=> ['string', 'in:' . implode(',', array_keys(LetterTemplateService::AVAILABLE_PLACEHOLDERS))],
        ];
    }

    public function messages(): array
    {
        return [
            'letter_type_id.required'  => 'Jenis surat wajib dipilih.',
            'letter_type_id.exists'    => 'Jenis surat tidak valid.',
            'name.required'            => 'Nama template wajib diisi.',
            'content.required'         => 'Konten template wajib diisi.',
            'available_placeholders.*' => 'Placeholder tidak valid.',
        ];
    }
}