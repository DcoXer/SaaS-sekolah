<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('tu_keuangan');
    }

    public function rules(): array
    {
        return [
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'name'             => ['required', 'string', 'max:100'],
            'cycle'            => ['required', 'in:monthly,yearly,once'],
            'amount'           => ['required', 'integer', 'min:1000'],
            'due_date'         => ['required', 'date'],
            'grade'            => ['nullable', 'integer', 'between:1,6'],
            'is_exam_related'  => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'academic_year_id.required' => 'Tahun ajaran wajib dipilih.',
            'name.required'             => 'Nama jenis tagihan wajib diisi.',
            'cycle.required'            => 'Siklus tagihan wajib dipilih.',
            'cycle.in'                  => 'Siklus tagihan tidak valid.',
            'amount.required'           => 'Nominal tagihan wajib diisi.',
            'amount.min'                => 'Nominal tagihan minimal Rp1.000.',
            'due_date.required'         => 'Tanggal jatuh tempo wajib diisi.',
            'grade.between'             => 'Kelas harus antara 1-6.',
        ];
    }
}