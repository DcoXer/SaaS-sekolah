<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePpdbRegistrationRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            // Data siswa
            'full_name'       => 'required|string|max:255',
            'nik_siswa'       => 'nullable|string|digits:16',
            'no_kk'           => 'required|string|digits:16',
            'birth_place'     => 'required|string|max:100',
            'birth_date'      => 'required|date',
            'gender'          => 'required|in:male,female',
            'religion'        => 'nullable|string|max:50',
            'previous_school' => 'nullable|string|max:255',

            // Alamat
            'province'        => 'required|string|max:100',
            'regency'         => 'required|string|max:100',
            'district'        => 'required|string|max:100',
            'village'         => 'required|string|max:100',
            'address'         => 'required|string|max:500',

            // Data ayah
            'father_name'     => 'required|string|max:255',
            'father_nik'      => 'required|string|digits:16',
            'father_phone'    => 'nullable|string|max:20',

            // Data ibu
            'mother_name'     => 'required|string|max:255',
            'mother_nik'      => 'required|string|digits:16',
            'mother_phone'    => 'nullable|string|max:20',

            // Kontak
            'parent_phone'    => 'required|string|max:20',
            'parent_email'    => 'nullable|email|max:255',

            // Dokumen (wajib)
            'photo'           => 'required|image|max:2048',
            'document_kk'     => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'document_akta'   => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ];
    }

    public function attributes(): array
    {
        return [
            'full_name'       => 'nama lengkap',
            'nik_siswa'       => 'NIK siswa',
            'no_kk'           => 'nomor kartu keluarga',
            'birth_place'     => 'tempat lahir',
            'birth_date'      => 'tanggal lahir',
            'gender'          => 'jenis kelamin',
            'province'        => 'provinsi',
            'regency'         => 'kabupaten/kota',
            'district'        => 'kecamatan',
            'village'         => 'kelurahan/desa',
            'address'         => 'detail alamat',
            'father_name'     => 'nama ayah',
            'father_nik'      => 'NIK ayah',
            'father_phone'    => 'nomor telepon ayah',
            'mother_name'     => 'nama ibu',
            'mother_nik'      => 'NIK ibu',
            'mother_phone'    => 'nomor telepon ibu',
            'parent_phone'    => 'nomor WhatsApp/HP utama',
            'parent_email'    => 'email kontak',
            'previous_school' => 'asal TK/RA',
            'photo'           => 'foto siswa',
            'document_kk'     => 'Kartu Keluarga',
            'document_akta'   => 'Akta Lahir',
        ];
    }
}
