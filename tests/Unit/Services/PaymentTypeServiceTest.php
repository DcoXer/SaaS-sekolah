<?php

namespace Tests\Unit\Services;

use App\Models\AcademicYear;
use App\Models\PaymentType;
use App\Services\PaymentTypeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTypeServiceTest extends TestCase
{
    use RefreshDatabase;

    private PaymentTypeService $service;
    private AcademicYear $academicYear;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PaymentTypeService();

        $this->academicYear = AcademicYear::create([
            'name'       => '2024/2025',
            'start_date' => '2024-07-15',
            'end_date'   => '2025-06-30',
            'status'     => 'active',
        ]);
    }

    public function test_can_create_payment_type(): void
    {
        $paymentType = $this->service->create([
            'academic_year_id' => $this->academicYear->id,
            'name'             => 'Uang Kegiatan',
            'cycle'            => 'yearly',
            'amount'           => 500000,
            'due_date'         => '2025-06-30',
            'grade'            => null,
            'is_exam_related'  => false,
        ]);

        $this->assertInstanceOf(PaymentType::class, $paymentType);
        $this->assertDatabaseHas('payment_types', ['name' => 'Uang Kegiatan']);
    }

    public function test_can_update_payment_type(): void
    {
        $paymentType = $this->service->create([
            'academic_year_id' => $this->academicYear->id,
            'name'             => 'Uang Kegiatan',
            'cycle'            => 'yearly',
            'amount'           => 500000,
            'due_date'         => '2025-06-30',
        ]);

        $updated = $this->service->update($paymentType, [
            'name'     => 'Uang Kegiatan Updated',
            'amount'   => 600000,
            'due_date' => '2025-06-30',
        ]);

        $this->assertEquals('Uang Kegiatan Updated', $updated->name);
        $this->assertEquals(600000, $updated->amount);
    }

    public function test_can_delete_payment_type(): void
    {
        $paymentType = $this->service->create([
            'academic_year_id' => $this->academicYear->id,
            'name'             => 'Uang Kegiatan',
            'cycle'            => 'yearly',
            'amount'           => 500000,
            'due_date'         => '2025-06-30',
        ]);

        $this->service->delete($paymentType);

        $this->assertDatabaseMissing('payment_types', ['id' => $paymentType->id]);
    }

    public function test_can_generate_monthly_spp(): void
    {
        $this->service->generateMonthlySpp($this->academicYear, 200000);

        $sppCount = PaymentType::where('academic_year_id', $this->academicYear->id)
                               ->where('cycle', 'monthly')
                               ->count();

        // Juli 2024 - Juni 2025 = 12 bulan
        $this->assertEquals(12, $sppCount);
    }

    public function test_generate_monthly_spp_is_idempotent(): void
    {
        $this->service->generateMonthlySpp($this->academicYear, 200000);
        $this->service->generateMonthlySpp($this->academicYear, 200000);

        $sppCount = PaymentType::where('academic_year_id', $this->academicYear->id)
                               ->where('cycle', 'monthly')
                               ->count();

        $this->assertEquals(12, $sppCount);
    }

    public function test_generate_monthly_spp_creates_correct_names(): void
    {
        $this->service->generateMonthlySpp($this->academicYear, 200000);

        $this->assertDatabaseHas('payment_types', ['name' => 'SPP Juli 2024']);
        $this->assertDatabaseHas('payment_types', ['name' => 'SPP Juni 2025']);
    }

    public function test_payment_type_with_grade_restriction(): void
    {
        $paymentType = $this->service->create([
            'academic_year_id' => $this->academicYear->id,
            'name'             => 'Uang Kegiatan Kelas 6',
            'cycle'            => 'yearly',
            'amount'           => 1000000,
            'due_date'         => '2025-06-30',
            'grade'            => 6,
        ]);

        $this->assertEquals(6, $paymentType->grade);
    }

    public function test_exam_related_payment_type(): void
    {
        $paymentType = $this->service->create([
            'academic_year_id' => $this->academicYear->id,
            'name'             => 'Uang Semester',
            'cycle'            => 'once',
            'amount'           => 300000,
            'due_date'         => '2024-12-01',
            'is_exam_related'  => true,
        ]);

        $this->assertTrue($paymentType->is_exam_related);
    }
}