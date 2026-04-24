<?php

namespace Tests\Feature\Http\Controllers\Siswa;

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

    private User $siswa;
    private User $operator;
    private Student $student;
    private AcademicYear $academicYear;

    protected function setUp(): void
    {
        parent::setUp();

        $this->siswa = User::factory()->create();
        $this->siswa->assignRole('siswa');

        $this->operator = User::factory()->create();
        $this->operator->assignRole('operator');

        $this->student = Student::create([
            'user_id' => $this->siswa->id,
            'nis'     => '001',
            'name'    => 'Ahmad',
            'gender'  => 'L',
            'grade'   => 4,
            'status'  => 'active',
        ]);

        $this->academicYear = AcademicYear::create([
            'name'       => '2024/2025',
            'start_date' => '2024-07-15',
            'end_date'   => '2025-06-30',
            'status'     => 'active',
        ]);
    }

    public function test_siswa_can_view_invoices(): void
    {
        $response = $this->actingAs($this->siswa)
                         ->get(route('siswa.invoices.index'));

        $response->assertStatus(200);
    }

    public function test_guest_cannot_view_invoices(): void
    {
        $response = $this->get(route('siswa.invoices.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_operator_cannot_view_siswa_invoices(): void
    {
        $response = $this->actingAs($this->operator)
                         ->get(route('siswa.invoices.index'));

        $response->assertStatus(403);
    }

    public function test_returns_404_if_user_has_no_student(): void
    {
        $userWithoutStudent = User::factory()->create();
        $userWithoutStudent->assignRole('siswa');

        $response = $this->actingAs($userWithoutStudent)
                         ->get(route('siswa.invoices.index'));

        $response->assertStatus(404);
    }

    public function test_returns_404_if_no_active_year(): void
    {
        $this->academicYear->update(['status' => 'closed']);

        $response = $this->actingAs($this->siswa)
                         ->get(route('siswa.invoices.index'));

        $response->assertStatus(404);
    }
}
