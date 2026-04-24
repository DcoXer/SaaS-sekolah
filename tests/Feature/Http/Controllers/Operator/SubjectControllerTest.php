<?php

namespace Tests\Feature\Http\Controllers\Operator;

use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubjectControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $operator;
    private User $guru;

    protected function setUp(): void
    {
        parent::setUp();

        $this->operator = User::factory()->create();
        $this->operator->assignRole('operator');

        $this->guru = User::factory()->create();
        $this->guru->assignRole('guru');
    }

    public function test_operator_can_view_subjects(): void
    {
        $response = $this->actingAs($this->operator)
                         ->get(route('operator.subjects.index'));

        $response->assertStatus(200);
    }

    public function test_operator_can_create_subject(): void
    {
        $response = $this->actingAs($this->operator)
                         ->post(route('operator.subjects.store'), [
                             'name'  => 'Matematika',
                             'grade' => 1,
                         ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('subjects', ['name' => 'Matematika']);
    }

    public function test_store_fails_with_invalid_grade(): void
    {
        $response = $this->actingAs($this->operator)
                         ->post(route('operator.subjects.store'), [
                             'name'  => 'Matematika',
                             'grade' => 7, // invalid, max 6
                         ]);

        $response->assertSessionHasErrors('grade');
    }

    public function test_operator_can_update_subject(): void
    {
        $subject = Subject::create(['name' => 'Matematika', 'grade' => 1]);

        $response = $this->actingAs($this->operator)
                         ->put(route('operator.subjects.update', $subject), [
                             'name'  => 'Matematika Updated',
                             'grade' => 2,
                         ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('subjects', ['name' => 'Matematika Updated']);
    }

    public function test_operator_can_delete_subject(): void
    {
        $subject = Subject::create(['name' => 'Matematika', 'grade' => 1]);

        $response = $this->actingAs($this->operator)
                         ->delete(route('operator.subjects.destroy', $subject));

        $response->assertRedirect();
        $this->assertDatabaseMissing('subjects', ['id' => $subject->id]);
    }

    public function test_guru_cannot_create_subject(): void
    {
        $response = $this->actingAs($this->guru)
                         ->post(route('operator.subjects.store'), [
                             'name'  => 'Matematika',
                             'grade' => 1,
                         ]);

        $response->assertStatus(403);
    }
}