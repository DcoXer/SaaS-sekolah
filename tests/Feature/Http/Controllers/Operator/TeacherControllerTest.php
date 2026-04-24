<?php

namespace Tests\Feature\Http\Controllers\Operator;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherControllerTest extends TestCase
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

    public function test_operator_can_view_teachers(): void
    {
        $response = $this->actingAs($this->operator)
                         ->get(route('operator.teachers.index'));

        $response->assertStatus(200);
    }

    public function test_guest_cannot_view_teachers(): void
    {
        $response = $this->get(route('operator.teachers.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_operator_can_create_teacher(): void
    {
        $response = $this->actingAs($this->operator)
                         ->post(route('operator.teachers.store'), [
                             'name'     => 'Budi Santoso',
                             'email'    => 'budi@sekolah.test',
                             'password' => 'password123',
                             'type'     => 'guru_bidang',
                             'gender'   => 'L',
                             'nip'      => '123456789',
                             'phone'    => '08123456789',
                         ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', ['email' => 'budi@sekolah.test']);
        $this->assertDatabaseHas('teachers', ['nip' => '123456789']);
    }

    public function test_store_fails_with_duplicate_email(): void
    {
        User::factory()->create(['email' => 'budi@sekolah.test']);

        $response = $this->actingAs($this->operator)
                         ->post(route('operator.teachers.store'), [
                             'name'     => 'Budi Santoso',
                             'email'    => 'budi@sekolah.test',
                             'password' => 'password123',
                             'gender'   => 'L',
                         ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_operator_can_update_teacher(): void
    {
        $teacher = $this->createTeacher();

        $response = $this->actingAs($this->operator)
                         ->put(route('operator.teachers.update', $teacher), [
                             'name'   => 'Budi Updated',
                             'email'  => 'budi@sekolah.test',
                             'type'   => 'guru_bidang',
                             'gender' => 'L',
                         ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', ['name' => 'Budi Updated']);
    }

    public function test_operator_can_delete_teacher(): void
    {
        $teacher = $this->createTeacher();
        $userId  = $teacher->user_id;

        $response = $this->actingAs($this->operator)
                         ->delete(route('operator.teachers.destroy', $teacher));

        $response->assertRedirect();
        $this->assertDatabaseMissing('teachers', ['id' => $teacher->id]);
        $this->assertDatabaseMissing('users', ['id' => $userId]);
    }

    public function test_kamad_cannot_create_teacher(): void
    {
        $response = $this->actingAs($this->kamad)
                         ->post(route('operator.teachers.store'), [
                             'name'     => 'Budi Santoso',
                             'email'    => 'budi@sekolah.test',
                             'password' => 'password123',
                             'gender'   => 'L',
                         ]);

        $response->assertStatus(403);
    }

    private function createTeacher(): Teacher
    {
        $user = User::factory()->create([
            'email' => 'budi@sekolah.test',
        ]);

        return Teacher::create([
            'user_id' => $user->id,
            'gender'  => 'L',
        ]);
    }
}