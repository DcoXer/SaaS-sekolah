<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExtracurricularRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('operator');
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'coach'       => ['nullable', 'string', 'max:150'],
            'schedule'    => ['nullable', 'string', 'max:200'],
            'image'       => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'is_active'   => ['boolean'],
            'sort_order'  => ['integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama ekskul wajib diisi.',
            'image.image'   => 'Foto harus berupa gambar.',
            'image.max'     => 'Ukuran foto maksimal 2MB.',
        ];
    }
}
