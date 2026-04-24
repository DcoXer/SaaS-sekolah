<?php

namespace Tests\Feature\Http\Controllers\Keuangan;

use App\Models\AcademicYear;
use App\Models\PaymentType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTypeControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $tuKeuangan;
    private User $operator;
    private AcademicYear $academicYear;

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
    }

    public function test_tu_keuangan_can_view_payment_types(): void
    {
        $response = $this->actingAs($this->tuKeuangan)
                         ->get(route('keuangan.payment-types.index'));

        $response->assertStatus(200);
    }

    public function test_guest_cannot_view_payment_types(): void
    {
        $response = $this->get(route('keuangan.payment-types.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_operator_cannot_view_payment_types(): void
    {
        $response = $this->actingAs($this->operator)
                         ->get(route('keuangan.payment-types.index'));

        $response->assertStatus(403);
    }

    public function test_tu_keuangan_can_create_payment_type(): void
    {
        $response = $this->actingAs($this->tuKeuangan)
                         ->post(route('keuangan.payment-types.store'), [
                             'academic_year_id' => $this->academicYear->id,
                             'name'             => 'Biaya Ujian',
                             'cycle'            => 'once',
                             'amount'           => 150000,
                             'due_date'         => '2024-11-30',
                             'is_exam_related'  => true,
                         ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('payment_types', ['name' => 'Biaya Ujian']);
    }

    public function test_store_fails_with_invalid_cycle(): void
    {
        $response = $this->actingAs($this->tuKeuangan)
                         ->post(route('keuangan.payment-types.store'), [
                             'academic_year_id' => $this->academicYear->id,
                             'name'             => 'Tagihan',
                             'cycle'            => 'invalid',
                             'amount'           => 100000,
                             'due_date'         => '2024-12-01',
                         ]);

        $response->assertSessionHasErrors('cycle');
    }

    public function test_store_fails_with_amount_too_small(): void
    {
        $response = $this->actingAs($this->tuKeuangan)
                         ->post(route('keuangan.payment-types.store'), [
                             'academic_year_id' => $this->academicYear->id,
                             'name'             => 'Tagihan',
                             'cycle'            => 'once',
                             'amount'           => 500,
                             'due_date'         => '2024-12-01',
                         ]);

        $response->assertSessionHasErrors('amount');
    }

    public function test_tu_keuangan_can_update_payment_type(): void
    {
        $paymentType = $this->createPaymentType();

        $response = $this->actingAs($this->tuKeuangan)
                         ->put(route('keuangan.payment-types.update', $paymentType), [
                             'name'     => 'Biaya Ujian Updated',
                             'amount'   => 200000,
                             'due_date' => '2024-12-31',
                         ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('payment_types', ['name' => 'Biaya Ujian Updated']);
    }

    public function test_tu_keuangan_can_delete_payment_type(): void
    {
        $paymentType = $this->createPaymentType();

        $response = $this->actingAs($this->tuKeuangan)
                         ->delete(route('keuangan.payment-types.destroy', $paymentType));

        $response->assertRedirect();
        $this->assertDatabaseMissing('payment_types', ['id' => $paymentType->id]);
    }

    public function test_tu_keuangan_can_generate_spp(): void
    {
        $response = $this->actingAs($this->tuKeuangan)
                         ->post(route('keuangan.payment-types.generate-spp'), [
                             'amount' => 200000,
                         ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    private function createPaymentType(): PaymentType
    {
        return PaymentType::create([
            'academic_year_id' => $this->academicYear->id,
            'name'             => 'Biaya Ujian',
            'cycle'            => 'once',
            'amount'           => 150000,
            'due_date'         => '2024-11-30',
            'is_exam_related'  => false,
            'is_active'        => true,
        ]);
    }
}
