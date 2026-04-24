<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClassroomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('operator');
    }

    public function rules(): array
    {
        return [
            'name'  => ['required', 'string', 'max:50'],
            'grade' => ['required', 'integer', 'between:1,6'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'  => 'Nama kelas wajib diisi.',
            'grade.required' => 'Tingkat kelas wajib diisi.',
            'grade.between'  => 'Tingkat kelas harus antara 1-6.',
        ];
    }
}