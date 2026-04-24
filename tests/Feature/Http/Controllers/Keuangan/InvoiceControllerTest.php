<?php

namespace Tests\Feature\Http\Controllers\Keuangan;

use App\Models\AcademicYear;
use App\Models\Invoice;
use App\Models\PaymentType;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $tuKeuangan;
    private User $operator;
    private AcademicYear $academicYear;
    private Student $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tuKeuangan = User::factory()->create();
        $this->tuKeuangan->assignRole('tu_keuangan');

        $this->operator = User::factory()->create();
        $this->operator->assignRole('operator');

        $this->academicYear = AcademicYear::create([
            'name'       => '2024/2025',
            'start_date' => '2024-07-15',
            'end_date'   => '2025-06-30',
            'status'     => 'active',
        ]);

        $this->student = Student::create([
            'nis'    => '001',
            'name'   => 'Ahmad',
            'gender' => 'L',
            'grade'  => 4,
            'status' => 'active',
        ]);
    }

    public function test_tu_keuangan_can_view_invoice_index(): void
    {
        $response = $this->actingAs($this->tuKeuangan)
                         ->get(route('keuangan.invoices.index'));

        $response->assertStatus(200);
    }

    public function test_guest_cannot_view_invoices(): void
    {
        $response = $this->get(route('keuangan.invoices.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_operator_cannot_view_invoices(): void
    {
        $response = $this->actingAs($this->operator)
                         ->get(route('keuangan.invoices.index'));

        $response->assertStatus(403);
    }

    public function test_tu_keuangan_can_view_student_invoices(): void
    {
        $response = $this->actingAs($this->tuKeuangan)
                         ->get(route('keuangan.invoices.show', $this->student));

        $response->assertStatus(200);
    }

    public function test_returns_404_when_no_active_year(): void
    {
        $this->academicYear->update(['status' => 'closed']);

        $response = $this->actingAs($this->tuKeuangan)
                         ->get(route('keuangan.invoices.show', $this->student));

        $response->assertStatus(404);
    }
}
