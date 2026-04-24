<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('tu_keuangan');
    }

    public function rules(): array
    {
        return [
            'name'            => ['required', 'string', 'max:100'],
            'amount'          => ['required', 'integer', 'min:1000'],
            'due_date'        => ['required', 'date'],
            'grade'           => ['nullable', 'integer', 'between:1,6'],
            'is_exam_related' => ['boolean'],
            'is_active'       => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'    => 'Nama jenis tagihan wajib diisi.',
            'amount.required'  => 'Nominal tagihan wajib diisi.',
            'amount.min'       => 'Nominal tagihan minimal Rp1.000.',
            'due_date.required'=> 'Tanggal jatuh tempo wajib diisi.',
            'grade.between'    => 'Kelas harus antara 1-6.',
        ];
    }
}