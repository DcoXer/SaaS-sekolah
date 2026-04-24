<?php

namespace Tests\Feature\Http\Controllers\Operator;

use App\Models\LetterTemplate;
use App\Models\LetterType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LetterTemplateControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $operator;
    private User $kamad;
    private LetterType $letterType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->operator = User::factory()->create();
        $this->operator->assignRole('operator');

        $this->kamad = User::factory()->create();
        $this->kamad->assignRole('kamad');

        $this->letterType = LetterType::create([
            'name'      => 'Surat Keterangan Aktif',
            'category'  => 'keterangan',
            'is_active' => true,
        ]);
    }

    public function test_operator_can_view_letter_templates(): void
    {
        $response = $this->actingAs($this->operator)
                         ->get(route('operator.letter-templates.index'));

        $response->assertStatus(200);
    }

    public function test_guest_cannot_view_letter_templates(): void
    {
        $response = $this->get(route('operator.letter-templates.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_kamad_cannot_view_letter_templates(): void
    {
        $response = $this->actingAs($this->kamad)
                         ->get(route('operator.letter-templates.index'));

        $response->assertStatus(403);
    }

    public function test_operator_can_create_letter_template(): void
    {
        $response = $this->actingAs($this->operator)
                         ->post(route('operator.letter-templates.store'), [
                             'letter_type_id' => $this->letterType->id,
                             'name'           => 'Template Keterangan Aktif',
                             'content'        => 'Yang bertanda tangan di bawah ini menerangkan bahwa {{student.name}} adalah siswa aktif.',
                         ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('letter_templates', ['name' => 'Template Keterangan Aktif']);
    }

    public function test_store_fails_without_name(): void
    {
        $response = $this->actingAs($this->operator)
                         ->post(route('operator.letter-templates.store'), [
                             'letter_type_id' => $this->letterType->id,
                             'content'        => 'Konten surat.',
                         ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_store_fails_with_invalid_letter_type(): void
    {
        $response = $this->actingAs($this->operator)
                         ->post(route('operator.letter-templates.store'), [
                             'letter_type_id' => 99999,
                             'name'           => 'Template',
                             'content'        => 'Konten.',
                         ]);

        $response->assertSessionHasErrors('letter_type_id');
    }

    public function test_operator_can_update_letter_template(): void
    {
        $template = LetterTemplate::create([
            'letter_type_id'          => $this->letterType->id,
            'name'                    => 'Template Lama',
            'content'                 => 'Konten lama.',
            'available_placeholders'  => [],
            'is_active'               => true,
        ]);

        $response = $this->actingAs($this->operator)
                         ->put(route('operator.letter-templates.update', $template), [
                             'name'    => 'Template Baru',
                             'content' => 'Konten baru.',
                         ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('letter_templates', ['name' => 'Template Baru']);
    }

    public function test_operator_can_delete_letter_template(): void
    {
        $template = LetterTemplate::create([
            'letter_type_id'         => $this->letterType->id,
            'name'                   => 'Template Hapus',
            'content'                => 'Konten.',
            'available_placeholders' => [],
            'is_active'              => true,
        ]);

        $response = $this->actingAs($this->operator)
                         ->delete(route('operator.letter-templates.destroy', $template));

        $response->assertRedirect();
        $this->assertDatabaseMissing('letter_templates', ['id' => $template->id]);
    }
}
