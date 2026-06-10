<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('operator');
    }

    public function rules(): array
    {
        $studentId = $this->route('student')->id;

        return [
            'nisn'        => ['required', 'string', 'max:20', "unique:students,nisn,{$studentId}"],
            'nis'         => ['nullable', 'string', 'max:20', "unique:students,nis,{$studentId}"],
            'name'        => ['required', 'string', 'max:100'],
            'gender'      => ['required', 'in:L,P'],
            'grade'       => ['required', 'integer', 'between:1,6'],
            'birth_place'   => ['nullable', 'string', 'max:100'],
            'birth_date'    => ['nullable', 'date'],
            'address'       => ['nullable', 'string'],
            'nik'           => ['nullable', 'string', 'max:20'],
            'father_name'   => ['nullable', 'string', 'max:100'],
            'mother_name'   => ['nullable', 'string', 'max:100'],
            'guardian_name' => ['nullable', 'string', 'max:100'],
            'parent_phone'  => ['nullable', 'string', 'max:20'],
            'parent_name'   => ['nullable', 'string', 'max:100'],
            'password'    => ['nullable', 'string', 'min:8', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'nisn.required'   => 'NISN wajib diisi.',
            'nisn.unique'     => 'NISN sudah terdaftar.',
            'nis.unique'      => 'NIS sudah terdaftar.',
            'name.required'   => 'Nama siswa wajib diisi.',
            'gender.required' => 'Jenis kelamin wajib diisi.',
            'gender.in'       => 'Jenis kelamin tidak valid.',
            'grade.required'  => 'Tingkat kelas wajib diisi.',
            'grade.between'   => 'Tingkat kelas harus antara 1-6.',
        ];
    }
}
