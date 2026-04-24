<?php

namespace Tests\Feature\Http\Controllers\Keuangan;

use App\Models\AcademicYear;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentType;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $tuKeuangan;
    private User $operator;
    private Student $student;
    private Invoice $invoice;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tuKeuangan = User::factory()->create();
        $this->tuKeuangan->assignRole('tu_keuangan');

        $this->operator = User::factory()->create();
        $this->operator->assignRole('operator');

        $academicYear = AcademicYear::create([
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

    public function test_tu_keuangan_can_record_payment(): void
    {
        $response = $this->actingAs($this->tuKeuangan)
                         ->post(route('keuangan.payments.store', $this->invoice), [
                             'amount' => 200000,
                         ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('payments', [
            'invoice_id' => $this->invoice->id,
            'amount'     => 200000,
        ]);
    }

    public function test_cannot_pay_more_than_remaining(): void
    {
        $response = $this->actingAs($this->tuKeuangan)
                         ->post(route('keuangan.payments.store', $this->invoice), [
                             'amount' => 999999,
                         ]);

        $response->assertSessionHasErrors('amount');
    }

    public function test_store_fails_with_amount_too_small(): void
    {
        $response = $this->actingAs($this->tuKeuangan)
                         ->post(route('keuangan.payments.store', $this->invoice), [
                             'amount' => 500,
                         ]);

        $response->assertSessionHasErrors('amount');
    }

    public function test_operator_cannot_record_payment(): void
    {
        $response = $this->actingAs($this->operator)
                         ->post(route('keuangan.payments.store', $this->invoice), [
                             'amount' => 200000,
                         ]);

        $response->assertStatus(403);
    }

    public function test_tu_keuangan_can_delete_payment(): void
    {
        $payment = Payment::create([
            'invoice_id'     => $this->invoice->id,
            'tu_keuangan_id' => $this->tuKeuangan->id,
            'amount'         => 100000,
            'method'         => 'cash',
            'paid_at'        => now(),
        ]);

        $response = $this->actingAs($this->tuKeuangan)
                         ->delete(route('keuangan.payments.destroy', $payment));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('payments', ['id' => $payment->id]);
    }

    public function test_tu_keuangan_can_view_receipt(): void
    {
        $response = $this->actingAs($this->tuKeuangan)
                         ->get(route('keuangan.payments.receipt', $this->invoice));

        $response->assertStatus(200);
    }

    public function test_guest_cannot_view_receipt(): void
    {
        $response = $this->get(route('keuangan.payments.receipt', $this->invoice));

        $response->assertRedirect(route('login'));
    }
}
