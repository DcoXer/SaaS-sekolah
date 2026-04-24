<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTeacherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('operator');
    }

    public function rules(): array
    {
        $teacherId = $this->route('teacher')->id;
        $userId = $this->route('teacher')->user_id;

        return [
            'name'     => ['required', 'string', 'max:100'],
            'email'    => ['required', 'email', "unique:users,email,{$userId}"],
            'password' => ['nullable', 'string', 'min:8'],
            'type'     => ['required', 'in:guru_kelas,guru_bidang'],
            'position' => ['nullable', 'in:wakamad_kesiswaan,wakamad_kurikulum'],
            'nip'      => ['nullable', 'string', 'max:20', "unique:teachers,nip,{$teacherId}"],
            'gender'   => ['required', 'in:L,P'],
            'phone'    => ['nullable', 'string', 'max:15'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'  => 'Nama guru wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique'   => 'Email sudah terdaftar.',
            'type.required'  => 'Jenis guru wajib dipilih.',
            'type.in'        => 'Jenis guru tidak valid.',
            'gender.required'=> 'Jenis kelamin wajib diisi.',
            'gender.in'      => 'Jenis kelamin tidak valid.',
        ];
    }
}