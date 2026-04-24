<?php

namespace Tests\Feature\Http\Controllers\Operator;

use App\Models\AcademicYear;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicYearControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $operator;
    private User $kamad;
    private User $guru;

    protected function setUp(): void
    {
        parent::setUp();

        $this->operator = User::factory()->create();
        $this->operator->assignRole('operator');

        $this->kamad = User::factory()->create();
        $this->kamad->assignRole('kamad');

        $this->guru = User::factory()->create();
        $this->guru->assignRole('guru');
    }

    // ========== INDEX ==========
    public function test_operator_can_view_academic_years(): void
    {
        $response = $this->actingAs($this->operator)
                         ->get(route('operator.academic-years.index'));

        $response->assertStatus(200);
    }

    public function test_guest_cannot_view_academic_years(): void
    {
        $response = $this->get(route('operator.academic-years.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_guru_cannot_access_operator_academic_years(): void
    {
        $response = $this->actingAs($this->guru)
                         ->get(route('operator.academic-years.index'));

        $response->assertStatus(403);
    }

    // ========== STORE ==========
    public function test_operator_can_create_academic_year(): void
    {
        $response = $this->actingAs($this->operator)
                         ->post(route('operator.academic-years.store'), [
                             'name'       => '2024/2025',
                             'start_date' => '2024-07-15',
                             'end_date'   => '2025-06-30',
                         ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('academic_years', [
            'name'   => '2024/2025',
            'status' => 'pending',
        ]);
    }

    public function test_operator_cannot_create_duplicate_academic_year(): void
    {
        AcademicYear::create([
            'name'       => '2024/2025',
            'start_date' => '2024-07-15',
            'end_date'   => '2025-06-30',
            'status'     => 'pending',
        ]);

        $response = $this->actingAs($this->operator)
                         ->post(route('operator.academic-years.store'), [
                             'name'       => '2024/2025',
                             'start_date' => '2024-07-15',
                             'end_date'   => '2025-06-30',
                         ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_store_fails_with_invalid_dates(): void
    {
        $response = $this->actingAs($this->operator)
                         ->post(route('operator.academic-years.store'), [
                             'name'       => '2024/2025',
                             'start_date' => '2024-07-15',
                             'end_date'   => '2024-06-01', // before start_date
                         ]);

        $response->assertSessionHasErrors('end_date');
    }

    public function test_kamad_cannot_create_academic_year(): void
    {
        $response = $this->actingAs($this->kamad)
                         ->post(route('operator.academic-years.store'), [
                             'name'       => '2024/2025',
                             'start_date' => '2024-07-15',
                             'end_date'   => '2025-06-30',
                         ]);

        $response->assertStatus(403);
    }
}