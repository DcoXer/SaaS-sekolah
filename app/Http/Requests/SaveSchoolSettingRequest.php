<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveSchoolSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('operator');
    }

    public function rules(): array
    {
        return [
            'name'           => ['required', 'string', 'max:200'],
            'tagline'        => ['nullable', 'string', 'max:255'],
            'npsn'           => ['nullable', 'string', 'max:20'],
            'principal_name' => ['required', 'string', 'max:100'],
            'principal_nip'  => ['nullable', 'string', 'max:30'],
            'address'        => ['required', 'string'],
            'phone'          => ['nullable', 'string', 'max:20'],
            'email'          => ['nullable', 'email'],
            'website'        => ['nullable', 'url'],
            'description'    => ['nullable', 'string'],
            'vision'         => ['nullable', 'string'],
            'mission'        => ['nullable', 'string'],
            'history'        => ['nullable', 'string'],
            'logo'           => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'stamp'          => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'           => 'Nama sekolah wajib diisi.',
            'principal_name.required' => 'Nama kepala sekolah wajib diisi.',
            'address.required'        => 'Alamat sekolah wajib diisi.',
            'logo.image'              => 'Logo harus berupa gambar.',
            'logo.max'                => 'Ukuran logo maksimal 2MB.',
            'stamp.image'             => 'Stempel harus berupa gambar.',
            'stamp.max'               => 'Ukuran stempel maksimal 2MB.',
        ];
    }
}