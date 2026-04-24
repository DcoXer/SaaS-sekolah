<?php

namespace Tests\Unit\Services;

use App\Models\LetterType;
use App\Services\LetterTypeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LetterTypeServiceTest extends TestCase
{
    use RefreshDatabase;

    private LetterTypeService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new LetterTypeService();
    }

    public function test_can_create_letter_type(): void
    {
        $letterType = $this->service->create([
            'name'        => 'Surat Keterangan',
            'category'    => 'keterangan',
            'description' => 'Surat keterangan siswa',
        ]);

        $this->assertInstanceOf(LetterType::class, $letterType);
        $this->assertDatabaseHas('letter_types', ['name' => 'Surat Keterangan']);
    }

    public function test_can_update_letter_type(): void
    {
        $letterType = $this->service->create([
            'name'     => 'Surat Keterangan',
            'category' => 'keterangan',
        ]);

        $updated = $this->service->update($letterType, [
            'name'     => 'Surat Keterangan Updated',
            'category' => 'keterangan',
        ]);

        $this->assertEquals('Surat Keterangan Updated', $updated->name);
    }

    public function test_can_delete_letter_type(): void
    {
        $letterType = $this->service->create([
            'name'     => 'Surat Keterangan',
            'category' => 'keterangan',
        ]);

        $this->service->delete($letterType);

        $this->assertDatabaseMissing('letter_types', ['id' => $letterType->id]);
    }

    public function test_get_active_returns_only_active(): void
    {
        $this->service->create([
            'name'     => 'Surat Keterangan',
            'category' => 'keterangan',
        ]);

        $inactive = $this->service->create([
            'name'     => 'Surat Pemberitahuan',
            'category' => 'pemberitahuan',
        ]);

        $this->service->update($inactive, [
            'name'      => 'Surat Pemberitahuan',
            'category'  => 'pemberitahuan',
            'is_active' => false,
        ]);

        $active = $this->service->getActive();

        $this->assertCount(1, $active);
        $this->assertEquals('Surat Keterangan', $active->first()->name);
    }

    public function test_letter_type_category(): void
    {
        $keterangan = $this->service->create([
            'name'     => 'Surat Keterangan',
            'category' => 'keterangan',
        ]);

        $pemberitahuan = $this->service->create([
            'name'     => 'Surat Pemberitahuan',
            'category' => 'pemberitahuan',
        ]);

        $this->assertTrue($keterangan->isKeterangan());
        $this->assertFalse($keterangan->isPemberitahuan());
        $this->assertTrue($pemberitahuan->isPemberitahuan());
        $this->assertFalse($pemberitahuan->isKeterangan());
    }
}