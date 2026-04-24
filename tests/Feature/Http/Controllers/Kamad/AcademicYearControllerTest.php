<?php

namespace Tests\Feature\Http\Controllers\Kamad;

use App\Models\AcademicYear;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicYearControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $kamad;
    private User $operator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kamad = User::factory()->create();
        $this->kamad->assignRole('kamad');

        $this->operator = User::factory()->create();
        $this->operator->assignRole('operator');
    }

    public function test_kamad_can_view_academic_years(): void
    {
        $response = $this->actingAs($this->kamad)
                         ->get(route('kamad.academic-years.index'));

        $response->assertStatus(200);
    }

    public function test_operator_cannot_access_kamad_academic_years(): void
    {
        $response = $this->actingAs($this->operator)
                         ->get(route('kamad.academic-years.index'));

        $response->assertStatus(403);
    }

    public function test_kamad_can_approve_pending_academic_year(): void
    {
        $pending = AcademicYear::create([
            'name'       => '2024/2025',
            'start_date' => '2024-07-15',
            'end_date'   => '2025-06-30',
            'status'     => 'pending',
        ]);

        $response = $this->actingAs($this->kamad)
                         ->patch(route('kamad.academic-years.approve', $pending));

        $response->assertRedirect();
        $this->assertEquals('active', $pending->fresh()->status);
    }

    public function test_kamad_cannot_approve_already_active_academic_year(): void
    {
        $active = AcademicYear::create([
            'name'       => '2024/2025',
            'start_date' => '2024-07-15',
            'end_date'   => '2025-06-30',
            'status'     => 'active',
        ]);

        $response = $this->actingAs($this->kamad)
                         ->patch(route('kamad.academic-years.approve', $active));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_approve_closes_previous_active_year(): void
    {
        $active = AcademicYear::create([
            'name'       => '2023/2024',
            'start_date' => '2023-07-15',
            'end_date'   => '2024-06-30',
            'status'     => 'active',
        ]);

        $pending = AcademicYear::create([
            'name'       => '2024/2025',
            'start_date' => '2024-07-15',
            'end_date'   => '2025-06-30',
            'status'     => 'pending',
        ]);

        $this->actingAs($this->kamad)
             ->patch(route('kamad.academic-years.approve', $pending));

        $this->assertEquals('closed', $active->fresh()->status);
        $this->assertEquals('active', $pending->fresh()->status);
    }

    public function test_operator_cannot_approve_academic_year(): void
    {
        $pending = AcademicYear::create([
            'name'       => '2024/2025',
            'start_date' => '2024-07-15',
            'end_date'   => '2025-06-30',
            'status'     => 'pending',
        ]);

        $response = $this->actingAs($this->operator)
                         ->patch(route('kamad.academic-years.approve', $pending));

        $response->assertStatus(403);
    }
}