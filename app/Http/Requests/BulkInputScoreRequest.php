<?php

namespace App\Http\Requests;

use App\Models\AssessmentComponent;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkInputScoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('guru');
    }

    public function rules(): array
    {
        // Ambil classroom_id dari assessmentComponent yang di-route
        $component   = $this->route('assessmentComponent');
        $classroomId = $component instanceof AssessmentComponent
            ? $component->classroom_id
            : null;

        return [
            'scores'               => ['required', 'array', 'min:1'],
            'scores.*.student_id'  => [
                'required',
                // Siswa harus terdaftar di kelas yang sama dengan komponen nilai
                Rule::exists('student_classrooms', 'student_id')
                    ->where('classroom_id', $classroomId),
            ],
            'scores.*.score'       => ['nullable', 'integer', 'min:0', 'max:100'],
            'scores.*.predicate'   => ['nullable', 'string', 'max:5'],
            'scores.*.narrative'   => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'scores.required'              => 'Data nilai wajib diisi.',
            'scores.*.student_id.required' => 'ID siswa wajib ada.',
            'scores.*.student_id.exists'   => 'Siswa tidak valid.',
            'scores.*.score.max'           => 'Nilai maksimal 100.',
        ];
    }
}