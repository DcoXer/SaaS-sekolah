<?php

namespace Tests\Feature\Http\Controllers\Operator;

use App\Models\LetterType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LetterTypeControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $operator;
    private User $kamad;

    protected function setUp(): void
    {
        parent::setUp();

        $this->operator = User::factory()->create();
        $this->operator->assignRole('operator');

        $this->kamad = User::factory()->create();
        $this->kamad->assignRole('kamad');
    }

    public function test_operator_can_view_letter_types(): void
    {
        $response = $this->actingAs($this->operator)
                         ->get(route('operator.letter-types.index'));

        $response->assertStatus(200);
    }

    public function test_guest_cannot_view_letter_types(): void
    {
        $response = $this->get(route('operator.letter-types.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_kamad_cannot_view_letter_types(): void
    {
        $response = $this->actingAs($this->kamad)
                         ->get(route('operator.letter-types.index'));

        $response->assertStatus(403);
    }

    public function test_operator_can_create_letter_type(): void
    {
        $response = $this->actingAs($this->operator)
                         ->post(route('operator.letter-types.store'), [
                             'name'     => 'Surat Keterangan Aktif',
                             'category' => 'keterangan',
                         ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('letter_types', ['name' => 'Surat Keterangan Aktif']);
    }

    public function test_store_fails_with_invalid_category(): void
    {
        $response = $this->actingAs($this->operator)
                         ->post(route('operator.letter-types.store'), [
                             'name'     => 'Surat Test',
                             'category' => 'invalid_category',
                         ]);

        $response->assertSessionHasErrors('category');
    }

    public function test_store_fails_without_name(): void
    {
        $response = $this->actingAs($this->operator)
                         ->post(route('operator.letter-types.store'), [
                             'category' => 'keterangan',
                         ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_operator_can_update_letter_type(): void
    {
        $letterType = LetterType::create([
            'name'      => 'Surat Keterangan Aktif',
            'category'  => 'keterangan',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->operator)
                         ->put(route('operator.letter-types.update', $letterType), [
                             'name'     => 'Surat Keterangan Aktif Updated',
                             'category' => 'keterangan',
                         ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('letter_types', ['name' => 'Surat Keterangan Aktif Updated']);
    }

    public function test_operator_can_delete_letter_type(): void
    {
        $letterType = LetterType::create([
            'name'      => 'Surat Keterangan',
            'category'  => 'keterangan',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->operator)
                         ->delete(route('operator.letter-types.destroy', $letterType));

        $response->assertRedirect();
        $this->assertDatabaseMissing('letter_types', ['id' => $letterType->id]);
    }
}
