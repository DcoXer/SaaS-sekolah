<?php

namespace App\Services;

use App\Models\SchoolSetting;
use Illuminate\Support\Facades\Storage;

class SchoolSettingService
{
    public function get(): ?SchoolSetting
    {
        return SchoolSetting::current();
    }

    public function save(array $data): SchoolSetting
    {
        $setting = SchoolSetting::first() ?? new SchoolSetting();

        // Simpan file baru DULU sebelum hapus yang lama — agar tidak kehilangan file jika upload crash
        $fileFields = [
            'logo'         => 'school/logo',
            'stamp'        => 'school/stamp',
            'hero_welcome' => 'school/hero',
            'hero_tentang' => 'school/hero',
            'hero_galeri'  => 'school/hero',
            'hero_ekskul'  => 'school/hero',
        ];

        $oldFiles = [];

        foreach ($fileFields as $field => $folder) {
            if (!empty($data[$field]) && is_object($data[$field])) {
                $oldFiles[$field] = $setting->{$field};
                $data[$field]     = $data[$field]->store($folder, 'public');
            } else {
                // Jangan overwrite file yang sudah ada jika tidak ada file baru yang diupload
                unset($data[$field]);
            }
        }

        $setting->fill($data);
        $setting->save();

        // Hapus file lama setelah setting berhasil disimpan
        foreach ($oldFiles as $field => $oldPath) {
            if ($oldPath) {
                Storage::disk('public')->delete($oldPath);
            }
        }

        return $setting;
    }
}