<?php

namespace Tests\Unit\Services;

use App\Models\AcademicYear;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentType;
use App\Models\Student;
use App\Models\User;
use App\Services\InvoiceService;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    private PaymentService $service;
    private AcademicYear $academicYear;
    private Student $student;
    private Invoice $invoice;
    private User $tuUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new PaymentService(new InvoiceService());

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
            'status' => 'active',
        ]);

        $paymentType = PaymentType::create([
            'academic_year_id' => $this->academicYear->id,
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
            'academic_year_id' => $this->academicYear->id,
            'amount'           => 200000,
            'status'           => 'unpaid',
            'due_date'         => '2024-07-31',
        ]);

        $this->tuUser = User::factory()->create();
        $this->tuUser->assignRole('tu_keuangan');
    }

    public function test_can_record_cash_payment(): void
    {
        $payment = $this->service->recordCashPayment(
            $this->invoice,
            $this->tuUser,
            ['amount' => 200000]
        );

        $this->assertInstanceOf(Payment::class, $payment);
        $this->assertDatabaseHas('payments', [
            'invoice_id'     => $this->invoice->id,
            'amount'         => 200000,
            'method'         => 'cash',
        ]);
    }

    public function test_cash_payment_updates_invoice_status_to_paid(): void
    {
        $this->service->recordCashPayment(
            $this->invoice,
            $this->tuUser,
            ['amount' => 200000]
        );

        $this->assertEquals('paid', $this->invoice->fresh()->status);
    }

    public function test_partial_cash_payment_updates_invoice_status_to_partial(): void
    {
        $this->service->recordCashPayment(
            $this->invoice,
            $this->tuUser,
            ['amount' => 100000]
        );

        $this->assertEquals('partial', $this->invoice->fresh()->status);
    }

    public function test_can_record_cash_payment_with_proof_file(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('proof.jpg');

        $payment = $this->service->recordCashPayment(
            $this->invoice,
            $this->tuUser,
            [
                'amount'     => 200000,
                'proof_file' => $file,
            ]
        );

        $this->assertNotNull($payment->proof_file);
        Storage::disk('public')->assertExists($payment->proof_file);
    }

    public function test_can_delete_payment(): void
    {
        $payment = Payment::create([
            'invoice_id'     => $this->invoice->id,
            'tu_keuangan_id' => $this->tuUser->id,
            'amount'         => 200000,
            'method'         => 'cash',
            'paid_at'        => now(),
        ]);

        // Set status ke paid dulu
        $this->invoice->update(['status' => 'paid']);

        $this->service->deletePayment($payment);

        $this->assertDatabaseMissing('payments', ['id' => $payment->id]);
        // Status harus balik ke unpaid
        $this->assertEquals('unpaid', $this->invoice->fresh()->status);
    }

    public function test_generate_receipt_data_returns_correct_structure(): void
    {
        Payment::create([
            'invoice_id'     => $this->invoice->id,
            'tu_keuangan_id' => $this->tuUser->id,
            'amount'         => 200000,
            'method'         => 'cash',
            'paid_at'        => now(),
        ]);

        $this->invoice->update(['status' => 'paid']);

        $receiptData = $this->service->generateReceiptData($this->invoice);

        $this->assertArrayHasKey('invoice', $receiptData);
        $this->assertArrayHasKey('student', $receiptData);
        $this->assertArrayHasKey('payment_type', $receiptData);
        $this->assertArrayHasKey('total_paid', $receiptData);
        $this->assertArrayHasKey('remaining', $receiptData);
        $this->assertArrayHasKey('status', $receiptData);
        $this->assertEquals(200000, $receiptData['total_paid']);
        $this->assertEquals(0, $receiptData['remaining']);
    }
}