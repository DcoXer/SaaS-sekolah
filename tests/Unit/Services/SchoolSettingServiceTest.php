<?php

namespace Tests\Unit\Services;

use App\Models\SchoolSetting;
use App\Services\SchoolSettingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SchoolSettingServiceTest extends TestCase
{
    use RefreshDatabase;

    private SchoolSettingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SchoolSettingService();
    }

    public function test_can_save_school_setting(): void
    {
        $this->service->save([
            'name'           => 'MI Darul Hasan',
            'principal_name' => 'Ahmad Fauzi',
            'address'        => 'Jl. Raya No. 1',
        ]);

        $this->assertDatabaseHas('school_settings', [
            'name'           => 'MI Darul Hasan',
            'principal_name' => 'Ahmad Fauzi',
        ]);
    }

    public function test_save_updates_existing_setting(): void
    {
        $this->service->save([
            'name'           => 'MI Darul Hasan',
            'principal_name' => 'Ahmad Fauzi',
            'address'        => 'Jl. Raya No. 1',
        ]);

        $this->service->save([
            'name'           => 'MI Darul Hasan Updated',
            'principal_name' => 'Ahmad Fauzi',
            'address'        => 'Jl. Raya No. 1',
        ]);

        $this->assertCount(1, SchoolSetting::all());
        $this->assertDatabaseHas('school_settings', [
            'name' => 'MI Darul Hasan Updated',
        ]);
    }

    public function test_can_save_with_logo(): void
    {
        Storage::fake('public');

        $logo = UploadedFile::fake()->image('logo.png');

        $setting = $this->service->save([
            'name'           => 'MI Darul Hasan',
            'principal_name' => 'Ahmad Fauzi',
            'address'        => 'Jl. Raya No. 1',
            'logo'           => $logo,
        ]);

        $this->assertNotNull($setting->logo);
        Storage::disk('public')->assertExists($setting->logo);
    }

    public function test_can_get_school_setting(): void
    {
        $this->service->save([
            'name'           => 'MI Darul Hasan',
            'principal_name' => 'Ahmad Fauzi',
            'address'        => 'Jl. Raya No. 1',
        ]);

        $setting = $this->service->get();

        $this->assertNotNull($setting);
        $this->assertEquals('MI Darul Hasan', $setting->name);
    }

    public function test_get_returns_null_when_no_setting(): void
    {
        $setting = $this->service->get();

        $this->assertNull($setting);
    }
}