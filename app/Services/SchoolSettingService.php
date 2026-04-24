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

        if (!empty($data['logo'])) {
            if ($setting->logo) {
                Storage::disk('public')->delete($setting->logo);
            }
            $data['logo'] = $data['logo']->store('school/logo', 'public');
        }

        if (!empty($data['stamp'])) {
            if ($setting->stamp) {
                Storage::disk('public')->delete($setting->stamp);
            }
            $data['stamp'] = $data['stamp']->store('school/stamp', 'public');
        }

        $setting->fill($data);
        $setting->save();

        return $setting;
    }
}