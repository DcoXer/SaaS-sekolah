<?php

namespace Tests\Unit\Services;

use App\Models\LetterTemplate;
use App\Models\LetterType;
use App\Services\LetterTemplateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LetterTemplateServiceTest extends TestCase
{
    use RefreshDatabase;

    private LetterTemplateService $service;
    private LetterType $letterType;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new LetterTemplateService();

        $this->letterType = LetterType::create([
            'name'      => 'Surat Keterangan',
            'category'  => 'keterangan',
            'is_active' => true,
        ]);
    }

    public function test_can_create_letter_template(): void
    {
        $template = $this->service->create([
            'letter_type_id' => $this->letterType->id,
            'name'           => 'Surat Keterangan Siswa Aktif',
            'content'        => 'Yang bertanda tangan menerangkan {{student.name}}',
        ]);

        $this->assertInstanceOf(LetterTemplate::class, $template);
        $this->assertDatabaseHas('letter_templates', [
            'name' => 'Surat Keterangan Siswa Aktif',
        ]);
    }

    public function test_can_update_letter_template(): void
    {
        $template = $this->service->create([
            'letter_type_id' => $this->letterType->id,
            'name'           => 'Surat Keterangan Siswa Aktif',
            'content'        => 'Konten lama',
        ]);

        $updated = $this->service->update($template, [
            'name'    => 'Surat Keterangan Siswa Aktif Updated',
            'content' => 'Konten baru',
        ]);

        $this->assertEquals('Surat Keterangan Siswa Aktif Updated', $updated->name);
        $this->assertEquals('Konten baru', $updated->content);
    }

    public function test_can_delete_letter_template(): void
    {
        $template = $this->service->create([
            'letter_type_id' => $this->letterType->id,
            'name'           => 'Surat Keterangan Siswa Aktif',
            'content'        => 'Konten surat',
        ]);

        $this->service->delete($template);

        $this->assertDatabaseMissing('letter_templates', ['id' => $template->id]);
    }

    public function test_available_placeholders_are_set_by_default(): void
    {
        $template = $this->service->create([
            'letter_type_id' => $this->letterType->id,
            'name'           => 'Template Test',
            'content'        => 'Konten test',
        ]);

        $this->assertNotEmpty($template->available_placeholders);
        $this->assertContains('{{student.name}}', $template->available_placeholders);
    }

    public function test_available_placeholders_constant_is_defined(): void
    {
        $placeholders = LetterTemplateService::AVAILABLE_PLACEHOLDERS;

        $this->assertArrayHasKey('{{student.name}}', $placeholders);
        $this->assertArrayHasKey('{{student.nis}}', $placeholders);
        $this->assertArrayHasKey('{{classroom.name}}', $placeholders);
        $this->assertArrayHasKey('{{principal.name}}', $placeholders);
        $this->assertArrayHasKey('{{barcode}}', $placeholders);
    }
}