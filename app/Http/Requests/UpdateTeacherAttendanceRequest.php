<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTeacherAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('guru');
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'in:hadir,izin,sakit,alpha'],
            'notes'  => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Status kehadiran wajib dipilih.',
            'status.in'       => 'Status kehadiran tidak valid.',
        ];
    }
}
