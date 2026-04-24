<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLetterTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('operator');
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:100'],
            'category'    => ['required', 'in:keterangan,pemberitahuan'],
            'description' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'     => 'Nama jenis surat wajib diisi.',
            'category.required' => 'Kategori surat wajib dipilih.',
            'category.in'       => 'Kategori surat tidak valid.',
        ];
    }
}