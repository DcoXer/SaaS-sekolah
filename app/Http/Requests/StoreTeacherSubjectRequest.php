<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTeacherSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('operator');
    }

    public function rules(): array
    {
        return [
            'teacher_id'       => ['required', 'exists:teachers,id'],
            'subject_id'       => ['required', 'exists:subjects,id'],
            'classroom_id'     => ['required', 'exists:classrooms,id'],
            'academic_year_id' => ['required', 'exists:academic_years,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'teacher_id.required'       => 'Guru wajib dipilih.',
            'teacher_id.exists'         => 'Guru tidak valid.',
            'subject_id.required'       => 'Mata pelajaran wajib dipilih.',
            'subject_id.exists'         => 'Mata pelajaran tidak valid.',
            'classroom_id.required'     => 'Kelas wajib dipilih.',
            'classroom_id.exists'       => 'Kelas tidak valid.',
            'academic_year_id.required' => 'Tahun ajaran wajib dipilih.',
            'academic_year_id.exists'   => 'Tahun ajaran tidak valid.',
        ];
    }
}