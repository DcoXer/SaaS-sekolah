<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RecordCashPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('tu_keuangan');
    }

    public function rules(): array
    {
        return [
            'amount'     => ['required', 'integer', 'min:1000'],
            'proof_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'note'       => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.required'  => 'Nominal pembayaran wajib diisi.',
            'amount.min'       => 'Nominal pembayaran minimal Rp1.000.',
            'proof_file.mimes' => 'Bukti pembayaran harus berupa jpg, jpeg, png, atau pdf.',
            'proof_file.max'   => 'Ukuran file maksimal 2MB.',
        ];
    }
}