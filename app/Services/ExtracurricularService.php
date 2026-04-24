<?php

namespace App\Services;

use App\Models\Extracurricular;
use Illuminate\Support\Facades\Storage;

class ExtracurricularService
{
    public function all()
    {
        return Extracurricular::ordered()->get();
    }

    public function store(array $data): Extracurricular
    {
        if (!empty($data['image'])) {
            $data['image'] = $data['image']->store('school/extracurricular', 'public');
        }

        return Extracurricular::create($data);
    }

    public function update(Extracurricular $extracurricular, array $data): Extracurricular
    {
        if (!empty($data['image'])) {
            if ($extracurricular->image) {
                Storage::disk('public')->delete($extracurricular->image);
            }
            $data['image'] = $data['image']->store('school/extracurricular', 'public');
        } else {
            unset($data['image']);
        }

        $extracurricular->update($data);

        return $extracurricular;
    }

    public function delete(Extracurricular $extracurricular): void
    {
        if ($extracurricular->image) {
            Storage::disk('public')->delete($extracurricular->image);
        }

        $extracurricular->delete();
    }
}
