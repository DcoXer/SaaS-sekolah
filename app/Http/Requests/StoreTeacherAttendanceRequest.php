<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTeacherAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('guru');
    }

    public function rules(): array
    {
        return [
            'date'      => ['required', 'date', 'before_or_equal:today'],
            'status'    => ['required', 'in:hadir,izin,sakit,alpha'],
            'notes'     => ['nullable', 'string', 'max:500'],
            'latitude'  => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ];
    }

    public function messages(): array
    {
        return [
            'date.required'          => 'Tanggal wajib diisi.',
            'date.date'              => 'Format tanggal tidak valid.',
            'date.before_or_equal'   => 'Tidak bisa input absensi untuk tanggal yang akan datang.',
            'status.required'        => 'Status kehadiran wajib dipilih.',
            'status.in'              => 'Status kehadiran tidak valid.',
        ];
    }
}
