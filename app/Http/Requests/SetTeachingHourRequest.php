<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SetTeachingHourRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('operator');
    }

    public function rules(): array
    {
        return [
            'teacher_id'           => ['required', 'exists:teachers,id'],
            'academic_year_id'     => ['required', 'exists:academic_years,id'],
            'total_hours'          => ['required', 'integer', 'min:1', 'max:999'],
            'hourly_rate'          => ['required', 'integer', 'min:0'],
            'daily_transport_rate' => ['required', 'integer', 'min:0'],
            'position_name'        => ['nullable', 'string', 'max:100'],
            'position_allowance'   => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'teacher_id.required'           => 'Guru wajib dipilih.',
            'teacher_id.exists'             => 'Guru tidak valid.',
            'academic_year_id.required'     => 'Tahun ajaran wajib dipilih.',
            'academic_year_id.exists'       => 'Tahun ajaran tidak valid.',
            'total_hours.required'          => 'Jumlah jam pelajaran wajib diisi.',
            'total_hours.integer'           => 'Jumlah jam pelajaran harus angka.',
            'total_hours.min'               => 'Jumlah jam pelajaran minimal 1.',
            'hourly_rate.required'          => 'Harga per jam wajib diisi.',
            'hourly_rate.integer'           => 'Harga per jam harus angka.',
            'hourly_rate.min'               => 'Harga per jam tidak boleh negatif.',
            'daily_transport_rate.required' => 'Uang transport per hari wajib diisi.',
            'daily_transport_rate.integer'  => 'Uang transport harus angka.',
            'daily_transport_rate.min'      => 'Uang transport tidak boleh negatif.',
        ];
    }
}
