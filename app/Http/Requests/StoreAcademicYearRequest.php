<?php

namespace App\Http\Requests;

use App\Models\AcademicYear;
use Illuminate\Foundation\Http\FormRequest;

class StoreAcademicYearRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('operator');
    }

    public function rules(): array
    {
        return [
            'name'       => ['required', 'string', 'max:20', 'unique:academic_years,name'],
            'start_date' => ['required', 'date'],
            'end_date'   => ['required', 'date', 'after:start_date'],
            // Validasi tidak overlap dengan tahun ajaran lain yang masih active/pending
            'start_date' => [
                'required', 'date',
                function ($attr, $value, $fail) {
                    $end = request('end_date');
                    if (!$end) return;
                    $overlap = AcademicYear::where('status', '!=', 'closed')
                        ->where(function ($q) use ($value, $end) {
                            $q->whereBetween('start_date', [$value, $end])
                              ->orWhereBetween('end_date', [$value, $end])
                              ->orWhere(function ($q2) use ($value, $end) {
                                  $q2->where('start_date', '<=', $value)
                                     ->where('end_date', '>=', $end);
                              });
                        })
                        ->exists();
                    if ($overlap) {
                        $fail('Rentang tanggal overlap dengan tahun ajaran lain yang masih aktif atau pending.');
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'       => 'Nama tahun ajaran wajib diisi.',
            'name.unique'         => 'Tahun ajaran sudah ada.',
            'start_date.required' => 'Tanggal mulai wajib diisi.',
            'end_date.after'      => 'Tanggal selesai harus setelah tanggal mulai.',
        ];
    }
}