<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSchoolGalleryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('operator');
    }

    public function rules(): array
    {
        return [
            'file'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'video_url'   => ['nullable', 'url', 'max:500'],
            'caption'     => ['nullable', 'string', 'max:200'],
            'sort_order'  => ['integer', 'min:0'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            if (empty($this->file('file')) && empty($this->input('video_url'))) {
                $v->errors()->add('file', 'Upload foto atau masukkan URL video YouTube.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'file.image' => 'File harus berupa gambar.',
            'file.max'   => 'Ukuran foto maksimal 5MB.',
            'video_url.url' => 'URL video tidak valid.',
        ];
    }
}
