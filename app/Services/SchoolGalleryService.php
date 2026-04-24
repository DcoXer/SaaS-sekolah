<?php

namespace App\Services;

use App\Models\SchoolGallery;
use Illuminate\Support\Facades\Storage;

class SchoolGalleryService
{
    public function all()
    {
        return SchoolGallery::ordered()->get();
    }

    public function store(array $data): SchoolGallery
    {
        if (!empty($data['file'])) {
            $data['file_path'] = $data['file']->store('school/gallery', 'public');
            $data['type']      = 'photo';
        } else {
            $data['type'] = 'video';
        }
        unset($data['file']);

        return SchoolGallery::create($data);
    }

    public function delete(SchoolGallery $gallery): void
    {
        if ($gallery->file_path) {
            Storage::disk('public')->delete($gallery->file_path);
        }

        $gallery->delete();
    }
}
