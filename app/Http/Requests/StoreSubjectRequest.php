<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('operator');
    }

    public function rules(): array
    {
        return [
            'name'  => ['required', 'string', 'max:100'],
            'grade' => ['required', 'integer', 'between:1,6'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'  => 'Nama mapel wajib diisi.',
            'grade.required' => 'Tingkat kelas wajib diisi.',
            'grade.between'  => 'Tingkat kelas harus antara 1-6.',
        ];
    }
}