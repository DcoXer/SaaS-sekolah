<?php

namespace Tests\Feature\Http\Controllers\Siswa;

use App\Models\AcademicYear;
use App\Models\Invoice;
use App\Models\PaymentType;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $siswa;
    private User $otherSiswa;
    private Student $student;
    private Invoice $invoice;

    protected function setUp(): void
    {
        parent::setUp();

        $this->siswa = User::factory()->create();
        $this->siswa->assignRole('siswa');

        $this->otherSiswa = User::factory()->create();
        $this->otherSiswa->assignRole('siswa');

        $this->student = Student::create([
            'user_id' => $this->siswa->id,
            'nis'     => '001',
            'name'    => 'Ahmad',
            'gender'  => 'L',
            'grade'   => 4,
            'status'  => 'active',
        ]);

        $academicYear = AcademicYear::create([
            'name'       => '2024/2025',
            'start_date' => '2024-07-15',
            'end_date'   => '2025-06-30',
            'status'     => 'active',
        ]);

        $paymentType = PaymentType::create([
            'academic_year_id' => $academicYear->id,
            'name'             => 'SPP Juli 2024',
            'cycle'            => 'monthly',
            'amount'           => 200000,
            'due_date'         => '2024-07-31',
            'is_exam_related'  => false,
            'is_active'        => true,
        ]);

        $this->invoice = Invoice::create([
            'student_id'       => $this->student->id,
            'payment_type_id'  => $paymentType->id,
            'academic_year_id' => $academicYear->id,
            'amount'           => 200000,
            'status'           => 'unpaid',
            'due_date'         => '2024-07-31',
        ]);
    }

    public function test_siswa_can_view_own_receipt(): void
    {
        $response = $this->actingAs($this->siswa)
                         ->get(route('siswa.payments.receipt', $this->invoice));

        $response->assertStatus(200);
    }

    public function test_guest_cannot_view_receipt(): void
    {
        $response = $this->get(route('siswa.payments.receipt', $this->invoice));

        $response->assertRedirect(route('login'));
    }

    public function test_siswa_cannot_view_other_student_receipt(): void
    {
        // otherSiswa has no associated Student, so student_id check will fail
        $response = $this->actingAs($this->otherSiswa)
                         ->get(route('siswa.payments.receipt', $this->invoice));

        $response->assertStatus(403);
    }
}
