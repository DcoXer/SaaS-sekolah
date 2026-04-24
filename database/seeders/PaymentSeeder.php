<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentType;
use App\Models\Student;
use App\Models\User;
use App\Services\InvoiceService;
use App\Services\PaymentTypeService;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    public function __construct(
        private PaymentTypeService $paymentTypeService,
        private InvoiceService $invoiceService,
    ) {}

    public function run(): void
    {
        $year      = AcademicYear::where('status', 'active')->firstOrFail();
        $keuangan  = User::role('tu_keuangan')->firstOrFail();

        // ── 1. Buat PaymentType ───────────────────────────────────────────────��

        // SPP bulanan Juli 2025 – Juni 2026 (12 bulan, Rp 150.000/bulan)
        $this->paymentTypeService->generateMonthlySpp($year, 450_000);

        // DSP (Daftar Ulang Sekolah) — sekali bayar, semua siswa, bukan exam-related
        $dsp = $this->paymentTypeService->create([
            'academic_year_id' => $year->id,
            'name'             => 'Dana Sumbangan Pendidikan (DSP)',
            'cycle'            => 'once',
            'amount'           => 4_000_000,
            'due_date'         => '2025-08-31',
            'grade'            => null,
            'is_exam_related'  => false,
        ]);

        // Biaya UTS — sekali bayar, exam-related
        $utsPaymentType = $this->paymentTypeService->create([
            'academic_year_id' => $year->id,
            'name'             => 'Biaya Ujian Tengah Semester Ganjil',
            'cycle'            => 'once',
            'amount'           => 1_500_000,
            'due_date'         => '2025-11-30',
            'grade'            => null,
            'is_exam_related'  => true,
        ]);

        // ── 2. Generate Invoice untuk semua PaymentType ───────────────────────
        $allPaymentTypes = PaymentType::where('academic_year_id', $year->id)->get();

        foreach ($allPaymentTypes as $pt) {
            $this->invoiceService->generateForPaymentType($pt);
        }

        // ── 3. Rekam Pembayaran (sebagian siswa bayar, sebagian belum) ─────────

        // Siswa yang punya akun wali murid:
        // wali.rizky, wali.nayla, wali.alya, wali.farhan, wali.naufal, dll.
        $rizky   = Student::where('nis', '2025001')->firstOrFail();
        $nayla   = Student::where('nis', '2025002')->firstOrFail();
        $alya    = Student::where('nis', '2025006')->firstOrFail();
        $farhan  = Student::where('nis', '2025007')->firstOrFail();
        $nabila  = Student::where('nis', '2025016')->firstOrFail();
        $hafidz  = Student::where('nis', '2025021')->firstOrFail();

        // Rizky — bayar SPP Jul–Sep + DSP lunas, UTS belum
        $this->bayarSpp($rizky, $year, $keuangan, ['Juli', 'Agustus', 'September', 'Oktober'], full: true);
        $this->bayarInvoice($rizky, $dsp->id, $keuangan, 4_000_000); // DSP lunas

        // Nayla — bayar SPP Jul–Agt lunas, Sep partial, DSP lunas, UTS lunas
        $this->bayarSpp($nayla, $year, $keuangan, ['Juli', 'Agustus'], full: true);
        $this->bayarSpp($nayla, $year, $keuangan, ['September'], full: false, partial: 75_000);
        $this->bayarInvoice($nayla, $dsp->id, $keuangan, 4_000_000);
        $this->bayarInvoice($nayla, $utsPaymentType->id, $keuangan, 1_500_000);

        // Alya — bayar SPP Jul lunas, belum bayar lainnya
        $this->bayarSpp($alya, $year, $keuangan, ['Juli'], full: true);

        // Farhan — belum bayar sama sekali (untuk test blokir UTS)

        // Nabila — bayar semua (SPP Jul–Okt + DSP + UTS) lunas
        $this->bayarSpp($nabila, $year, $keuangan, ['Juli', 'Agustus', 'September', 'Oktober'], full: true);
        $this->bayarInvoice($nabila, $dsp->id, $keuangan, 4_000_000);
        $this->bayarInvoice($nabila, $utsPaymentType->id, $keuangan, 1_500_000);

        // Hafidz — partial DSP
        $this->bayarInvoice($hafidz, $dsp->id, $keuangan, 300_000, partial: true);
    }

    private function bayarSpp(Student $student, AcademicYear $year, User $keuangan, array $months, bool $full, int $partial = 0): void
    {
        foreach ($months as $bulan) {
            $invoice = Invoice::whereHas('paymentType', function ($q) use ($year, $bulan) {
                $q->where('academic_year_id', $year->id)
                  ->where('cycle', 'monthly')
                  ->where('name', 'like', "%$bulan%");
            })
            ->where('student_id', $student->id)
            ->first();

            if (!$invoice) {
                return;
            }

            $amount = $full ? $invoice->amount : $partial;

            Payment::create([
                'invoice_id'    => $invoice->id,
                'tu_keuangan_id' => $keuangan->id,
                'amount'        => $amount,
                'method'        => 'cash',
                'paid_at'       => now()->subDays(rand(1, 30)),
                'note'          => 'Pembayaran tunai',
            ]);

            $this->invoiceService->recalculateStatus($invoice);
        }
    }

    private function bayarInvoice(Student $student, int $paymentTypeId, User $keuangan, int $amount, bool $partial = false): void
    {
        $invoice = Invoice::where('student_id', $student->id)
                          ->where('payment_type_id', $paymentTypeId)
                          ->first();

        if (!$invoice) {
            return;
        }

        Payment::create([
            'invoice_id'     => $invoice->id,
            'tu_keuangan_id' => $keuangan->id,
            'amount'         => $amount,
            'method'         => 'cash',
            'paid_at'        => now()->subDays(rand(1, 14)),
            'note'           => 'Pembayaran tunai',
        ]);

        $this->invoiceService->recalculateStatus($invoice);
    }
}
