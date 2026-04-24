<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTeacherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('operator');
    }

    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:100'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'type'     => ['required', 'in:guru_kelas,guru_bidang'],
            'nip'      => ['nullable', 'string', 'max:20', 'unique:teachers,nip'],
            'gender'   => ['required', 'in:L,P'],
            'phone'    => ['nullable', 'string', 'max:15'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'     => 'Nama guru wajib diisi.',
            'email.required'    => 'Email wajib diisi.',
            'email.unique'      => 'Email sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.min'      => 'Password minimal 8 karakter.',
            'type.required'     => 'Jenis guru wajib dipilih.',
            'type.in'           => 'Jenis guru tidak valid.',
            'gender.required'   => 'Jenis kelamin wajib diisi.',
            'gender.in'         => 'Jenis kelamin tidak valid.',
        ];
    }
}