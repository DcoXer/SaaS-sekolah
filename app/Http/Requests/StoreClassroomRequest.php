<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClassroomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('operator');
    }

    public function rules(): array
    {
        return [
            'academic_year_id'    => ['required', 'exists:academic_years,id'],
            'name'                => ['required', 'string', 'max:50'],
            'grade'               => ['required', 'integer', 'between:1,6'],
            'homeroom_teacher_id' => ['nullable', 'exists:teachers,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'academic_year_id.required' => 'Tahun ajaran wajib dipilih.',
            'academic_year_id.exists'   => 'Tahun ajaran tidak valid.',
            'name.required'             => 'Nama kelas wajib diisi.',
            'grade.required'            => 'Tingkat kelas wajib diisi.',
            'grade.between'             => 'Tingkat kelas harus antara 1-6.',
            'homeroom_teacher_id.exists'=> 'Guru wali kelas tidak valid.',
        ];
    }
}