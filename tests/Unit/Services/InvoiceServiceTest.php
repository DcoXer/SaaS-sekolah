<?php

namespace Tests\Unit\Services;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentType;
use App\Models\Student;
use App\Models\User;
use App\Services\InvoiceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceServiceTest extends TestCase
{
    use RefreshDatabase;

    private InvoiceService $service;
    private AcademicYear $academicYear;
    private Student $student;
    private PaymentType $paymentType;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new InvoiceService();

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

        $this->paymentType = PaymentType::create([
            'academic_year_id' => $this->academicYear->id,
            'name'             => 'Uang Kegiatan',
            'cycle'            => 'yearly',
            'amount'           => 500000,
            'due_date'         => '2025-06-30',
            'is_exam_related'  => false,
            'is_active'        => true,
        ]);
    }

    public function test_can_generate_invoice_for_payment_type(): void
    {
        $this->service->generateForPaymentType($this->paymentType);

        $this->assertDatabaseHas('invoices', [
            'student_id'      => $this->student->id,
            'payment_type_id' => $this->paymentType->id,
            'status'          => 'unpaid',
        ]);
    }

    public function test_generate_invoice_is_idempotent(): void
    {
        $this->service->generateForPaymentType($this->paymentType);
        $this->service->generateForPaymentType($this->paymentType);

        $this->assertCount(1, Invoice::all());
    }

    public function test_generate_invoice_only_for_eligible_grade(): void
    {
        $classroom = Classroom::create([
            'academic_year_id' => $this->academicYear->id,
            'name'             => 'Kelas 6A',
            'grade'            => 6,
        ]);

        $student6 = Student::create([
            'nis'    => '002',
            'name'   => 'Budi',
            'gender' => 'L',
            'status' => 'active',
        ]);

        $classroom->students()->attach($student6->id, [
            'academic_year_id' => $this->academicYear->id,
        ]);

        $paymentTypeGrade6 = PaymentType::create([
            'academic_year_id' => $this->academicYear->id,
            'name'             => 'Uang Kegiatan Kelas 6',
            'cycle'            => 'yearly',
            'amount'           => 1000000,
            'due_date'         => '2025-06-30',
            'grade'            => 6,
            'is_exam_related'  => false,
            'is_active'        => true,
        ]);

        $this->service->generateForPaymentType($paymentTypeGrade6);

        // Hanya student6 yang dapat invoice
        $this->assertDatabaseHas('invoices', ['student_id' => $student6->id]);
        $this->assertDatabaseMissing('invoices', ['student_id' => $this->student->id]);
    }

    public function test_recalculate_status_unpaid_when_no_payments(): void
    {
        $invoice = Invoice::create([
            'student_id'       => $this->student->id,
            'payment_type_id'  => $this->paymentType->id,
            'academic_year_id' => $this->academicYear->id,
            'amount'           => 500000,
            'status'           => 'unpaid',
            'due_date'         => '2025-06-30',
        ]);

        $this->service->recalculateStatus($invoice);

        $this->assertEquals('unpaid', $invoice->fresh()->status);
    }

    public function test_recalculate_status_partial_when_partially_paid(): void
    {
        $invoice = Invoice::create([
            'student_id'       => $this->student->id,
            'payment_type_id'  => $this->paymentType->id,
            'academic_year_id' => $this->academicYear->id,
            'amount'           => 500000,
            'status'           => 'unpaid',
            'due_date'         => '2025-06-30',
        ]);

        $tuUser = User::factory()->create();
        $tuUser->assignRole('tu_keuangan');

        Payment::create([
            'invoice_id'     => $invoice->id,
            'tu_keuangan_id' => $tuUser->id,
            'amount'         => 200000,
            'method'         => 'cash',
            'paid_at'        => now(),
        ]);

        $this->service->recalculateStatus($invoice);

        $this->assertEquals('partial', $invoice->fresh()->status);
    }

    public function test_recalculate_status_paid_when_fully_paid(): void
    {
        $invoice = Invoice::create([
            'student_id'       => $this->student->id,
            'payment_type_id'  => $this->paymentType->id,
            'academic_year_id' => $this->academicYear->id,
            'amount'           => 500000,
            'status'           => 'unpaid',
            'due_date'         => '2025-06-30',
        ]);

        $tuUser = User::factory()->create();
        $tuUser->assignRole('tu_keuangan');

        Payment::create([
            'invoice_id'     => $invoice->id,
            'tu_keuangan_id' => $tuUser->id,
            'amount'         => 500000,
            'method'         => 'cash',
            'paid_at'        => now(),
        ]);

        $this->service->recalculateStatus($invoice);

        $this->assertEquals('paid', $invoice->fresh()->status);
    }

    public function test_has_exam_access_returns_true_when_all_paid(): void
    {
        $examPaymentType = PaymentType::create([
            'academic_year_id' => $this->academicYear->id,
            'name'             => 'Uang Semester',
            'cycle'            => 'once',
            'amount'           => 300000,
            'due_date'         => '2024-12-01',
            'is_exam_related'  => true,
            'is_active'        => true,
        ]);

        $invoice = new Invoice([
            'student_id'       => $this->student->id,
            'payment_type_id'  => $examPaymentType->id,
            'academic_year_id' => $this->academicYear->id,
            'amount'           => 300000,
            'due_date'         => '2024-12-01',
        ]);
        $invoice->status = 'paid';
        $invoice->save();

        $hasAccess = $this->service->hasExamAccess($this->student, $this->academicYear);

        $this->assertTrue($hasAccess);
    }

    public function test_has_exam_access_returns_false_when_unpaid(): void
    {
        $examPaymentType = PaymentType::create([
            'academic_year_id' => $this->academicYear->id,
            'name'             => 'Uang Semester',
            'cycle'            => 'once',
            'amount'           => 300000,
            'due_date'         => '2024-12-01',
            'is_exam_related'  => true,
            'is_active'        => true,
        ]);

        Invoice::create([
            'student_id'       => $this->student->id,
            'payment_type_id'  => $examPaymentType->id,
            'academic_year_id' => $this->academicYear->id,
            'amount'           => 300000,
            'status'           => 'unpaid',
            'due_date'         => '2024-12-01',
        ]);

        $hasAccess = $this->service->hasExamAccess($this->student, $this->academicYear);

        $this->assertFalse($hasAccess);
    }

    public function test_get_summary_returns_correct_totals(): void
    {
        $tuUser = User::factory()->create();
        $tuUser->assignRole('tu_keuangan');

        $invoice = Invoice::create([
            'student_id'       => $this->student->id,
            'payment_type_id'  => $this->paymentType->id,
            'academic_year_id' => $this->academicYear->id,
            'amount'           => 500000,
            'status'           => 'partial',
            'due_date'         => '2025-06-30',
        ]);

        Payment::create([
            'invoice_id'     => $invoice->id,
            'tu_keuangan_id' => $tuUser->id,
            'amount'         => 200000,
            'method'         => 'cash',
            'paid_at'        => now(),
        ]);

        $summary = $this->service->getSummaryByAcademicYear($this->academicYear);

        $this->assertEquals(500000, $summary['total_amount']);
        $this->assertEquals(200000, $summary['total_paid']);
        $this->assertEquals(300000, $summary['total_unpaid']);
    }
}
