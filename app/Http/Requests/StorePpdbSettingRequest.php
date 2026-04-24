<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePpdbSettingRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title'              => 'required|string|max:255',
            'description'        => 'nullable|string',
            'requirements'       => 'nullable|string',
            'registration_start' => 'required|date',
            'registration_end'   => 'required|date|after_or_equal:registration_start',
            'announcement_date'  => 'nullable|date',
            'quota'              => 'required|integer|min:1',
            'is_open'            => 'required|boolean',
        ];
    }
}
