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
        if (!empty($data['logo'])) {
            $newLogo      = $data['logo']->store('school/logo', 'public');
            $oldLogo      = $setting->logo;
            $data['logo'] = $newLogo;
        }

        if (!empty($data['stamp'])) {
            $newStamp      = $data['stamp']->store('school/stamp', 'public');
            $oldStamp      = $setting->stamp;
            $data['stamp'] = $newStamp;
        }

        $setting->fill($data);
        $setting->save();

        // Hapus file lama setelah setting berhasil disimpan
        if (!empty($oldLogo)) {
            Storage::disk('public')->delete($oldLogo);
        }
        if (!empty($oldStamp)) {
            Storage::disk('public')->delete($oldStamp);
        }

        return $setting;
    }
}