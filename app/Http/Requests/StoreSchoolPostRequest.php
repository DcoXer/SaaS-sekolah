<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSchoolPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'max:255'],
            'excerpt'     => ['nullable', 'string', 'max:500'],
            'content'     => ['required', 'string'],
            'cover_image' => ['nullable', 'image', 'max:2048'],
            'category'    => ['required', 'in:berita,pengumuman'],
            'is_published' => ['boolean'],
        ];
    }
}
