<?php

namespace App\Services;

use App\Models\Extracurricular;
use App\Models\ExtracurricularAchievement;
use App\Models\ExtracurricularPhoto;
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

    public function storeAchievement(Extracurricular $extracurricular, array $data): ExtracurricularAchievement
    {
        return $extracurricular->achievements()->create($data);
    }

    public function updateAchievement(ExtracurricularAchievement $achievement, array $data): ExtracurricularAchievement
    {
        $achievement->update($data);

        return $achievement;
    }

    public function deleteAchievement(ExtracurricularAchievement $achievement): void
    {
        $achievement->delete();
    }

    public function storePhoto(Extracurricular $extracurricular, $file): ExtracurricularPhoto
    {
        $path = $file->store('school/extracurricular/photos', 'public');

        return $extracurricular->photos()->create(['path' => $path]);
    }

    public function deletePhoto(ExtracurricularPhoto $photo): void
    {
        Storage::disk('public')->delete($photo->path);
        $photo->delete();
    }
}
