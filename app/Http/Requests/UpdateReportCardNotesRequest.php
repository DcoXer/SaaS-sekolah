<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReportCardNotesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('guru');
    }

    public function rules(): array
    {
        return [
            'homeroom_notes'  => ['nullable', 'string'],
            'principal_notes' => ['nullable', 'string'],
        ];
    }
}