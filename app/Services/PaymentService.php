<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentRequest;
use App\Models\Student;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PaymentService
{
    public function __construct(
        private InvoiceService $invoiceService,
        private NotificationService $notif,
    ) {}

    public function createCashRequest(Invoice $invoice, Student $student): PaymentRequest
    {
        return PaymentRequest::updateOrCreate(
            ['invoice_id' => $invoice->id],
            ['student_id' => $student->id, 'status' => 'pending'],
        );
    }

    public function recordCashPayment(
        Invoice $invoice,
        User $tuKeuangan,
        array $data
    ): Payment {
        return DB::transaction(function () use ($invoice, $tuKeuangan, $data) {
            // Lock row untuk cegah race condition concurrent payment
            $invoice = Invoice::lockForUpdate()->findOrFail($invoice->id);

            // Re-check sisa tagihan di dalam transaction setelah lock
            $remaining = $invoice->amount - $invoice->payments()->sum('amount');
            abort_if($remaining <= 0, 422, 'Invoice sudah lunas.');
            abort_if($data['amount'] > $remaining, 422, 'Nominal melebihi sisa tagihan.');

            $proofPath = null;

            if (!empty($data['proof_file'])) {
                $proofPath = $data['proof_file']->store('payments/proofs', 'public');
            }

            $payment = Payment::create([
                'invoice_id'      => $invoice->id,
                'tu_keuangan_id'  => $tuKeuangan->id,
                'amount'          => $data['amount'],
                'method'          => 'cash',
                'proof_file'      => $proofPath,
                'note'            => $data['note'] ?? null,
                'paid_at'         => now(),
            ]);

            $this->invoiceService->recalculateStatus($invoice);

            // Mark payment request as processed if exists
            PaymentRequest::where('invoice_id', $invoice->id)
                ->where('status', 'pending')
                ->update(['status' => 'processed']);

            // Generate receipt code on first payment
            $invoice->refresh();
            if (!$invoice->receipt_code) {
                $invoice->update(['receipt_code' => Str::uuid()->toString()]);
            }

            return $payment;
        });
    }

    public function initiateMidtransPayment(Invoice $invoice, User $user): array
    {
        $orderId = 'INV-' . $invoice->id . '-' . time();

        \Midtrans\Config::$serverKey    = config('services.midtrans.server_key');
        \Midtrans\Config::$isProduction = config('services.midtrans.is_production');
        \Midtrans\Config::$isSanitized  = true;
        \Midtrans\Config::$is3ds        = true;

        \Illuminate\Support\Facades\Log::info('Midtrans initiate', [
            'order_id'      => $orderId,
            'is_production' => \Midtrans\Config::$isProduction,
            'server_key'    => substr(\Midtrans\Config::$serverKey, 0, 12) . '...',
        ]);

        $params = [
            'transaction_details' => [
                'order_id'     => $orderId,
                'gross_amount' => $invoice->remaining_amount,
            ],
            'customer_details' => [
                'first_name' => $user->name,
                'email'      => $user->email,
            ],
            'item_details' => [
                [
                    'id'       => $invoice->payment_type_id ?? 'ppdb',
                    'price'    => $invoice->remaining_amount,
                    'quantity' => 1,
                    'name'     => $invoice->paymentType?->name ?? 'Uang Masuk PPDB',
                ],
            ],
            'callbacks' => [
                'finish' => route('siswa.payments.finish', ['order_id' => $orderId]),
            ],
        ];

        $snapToken = \Midtrans\Snap::getSnapToken($params);

        return [
            'snap_token' => $snapToken,
            'order_id'   => $orderId,
        ];
    }

    public function verifyAndProcessMidtransPayment(Invoice $invoice, string $orderId): string
    {
        \Midtrans\Config::$serverKey    = config('services.midtrans.server_key');
        \Midtrans\Config::$isProduction = config('services.midtrans.is_production');

        \Illuminate\Support\Facades\Log::info('Midtrans Transaction::status query', [
            'order_id'      => $orderId,
            'is_production' => \Midtrans\Config::$isProduction,
            'server_key'    => substr(\Midtrans\Config::$serverKey, 0, 12) . '...',
        ]);

        $status = \Midtrans\Transaction::status($orderId);

        $payload = (array) $status;

        // Validasi order_id cocok dengan invoice
        abort_if(
            !preg_match('/^INV-(\d+)-\d+$/', $orderId, $m) || (int) $m[1] !== $invoice->id,
            422,
            'Order ID tidak cocok dengan invoice.'
        );

        $transactionStatus = $payload['transaction_status'] ?? null;

        if (in_array($transactionStatus, ['settlement', 'capture'])) {
            DB::transaction(function () use ($invoice, $orderId, $payload, $transactionStatus) {
                $existing = Payment::where('midtrans_order_id', $orderId)->first();
                if ($existing) {
                    return;
                }

                Payment::create([
                    'invoice_id'        => $invoice->id,
                    'tu_keuangan_id'    => null,
                    'amount'            => (int) $payload['gross_amount'],
                    'method'            => 'midtrans',
                    'midtrans_order_id' => $orderId,
                    'midtrans_status'   => $transactionStatus,
                    'paid_at'           => now(),
                ]);

                $this->invoiceService->recalculateStatus($invoice);

                if (!$invoice->receipt_code) {
                    $invoice->update(['receipt_code' => \Illuminate\Support\Str::uuid()->toString()]);
                }
            });
        }

        return $transactionStatus ?? 'unknown';
    }

    /**
     * Record payment berdasarkan transaction_status dari Midtrans finish redirect.
     * Digunakan sebagai last-resort fallback ketika Transaction::status() API gagal
     * tapi redirect URL sudah membawa transaction_status settlement/capture.
     *
     * Amount diambil dari invoice.remaining_amount karena gross_amount tidak tersedia
     * di redirect URL (hanya ada di webhook & API response).
     */
    public function recordPaymentFromRedirect(Invoice $invoice, string $orderId, string $transactionStatus): void
    {
        DB::transaction(function () use ($invoice, $orderId, $transactionStatus) {
            $existing = Payment::where('midtrans_order_id', $orderId)->first();
            if ($existing) {
                return;
            }

            // Lock invoice sebelum baca remaining_amount — cegah concurrent double-record
            $invoice = Invoice::lockForUpdate()->findOrFail($invoice->id);
            $amount  = $invoice->remaining_amount;

            Payment::create([
                'invoice_id'        => $invoice->id,
                'tu_keuangan_id'    => null,
                'amount'            => $amount,
                'method'            => 'midtrans',
                'midtrans_order_id' => $orderId,
                'midtrans_status'   => $transactionStatus,
                'paid_at'           => now(),
            ]);

            $this->invoiceService->recalculateStatus($invoice);

            $invoice->refresh();
            if (!$invoice->receipt_code) {
                $invoice->update(['receipt_code' => Str::uuid()->toString()]);
            }
        });
    }

    public function handleMidtransCallback(array $payload): void
    {
        // Verifikasi signature Midtrans sebelum proses apapun
        $this->verifyMidtransSignature($payload);

        DB::transaction(function () use ($payload) {
            $orderId = $payload['order_id'];

            // Validasi format order_id: harus "INV-{numeric}-{timestamp}"
            if (!preg_match('/^INV-(\d+)-\d+$/', $orderId, $matches)) {
                abort(400, 'Invalid order_id format.');
            }

            $invoiceId = (int) $matches[1];
            $invoice   = Invoice::findOrFail($invoiceId);

            // Validasi gross_amount tidak melebihi total tagihan (anti-tamper sanity check)
            $grossAmount = (int) ($payload['gross_amount'] ?? 0);
            abort_if(
                $grossAmount <= 0 || $grossAmount > $invoice->amount,
                400,
                'Nominal pembayaran tidak valid.'
            );

            // Cek apakah sudah ada payment dengan order_id ini
            $existing = Payment::where('midtrans_order_id', $orderId)->first();

            if ($existing) {
                $existing->update(['midtrans_status' => $payload['transaction_status']]);
                $this->invoiceService->recalculateStatus($invoice);
                return;
            }

            if (in_array($payload['transaction_status'], ['settlement', 'capture'])) {
                Payment::create([
                    'invoice_id'        => $invoice->id,
                    'tu_keuangan_id'    => null, // online payment — tidak ada TU yang konfirmasi manual
                    'amount'            => (int) $payload['gross_amount'],
                    'method'            => 'midtrans',
                    'midtrans_order_id' => $orderId,
                    'midtrans_status'   => $payload['transaction_status'],
                    'paid_at'           => now(),
                ]);

                $this->invoiceService->recalculateStatus($invoice);

                // Notifikasi ke siswa bahwa pembayaran online berhasil
                $invoice->load('student.user', 'paymentType');
                if ($invoice->student?->user) {
                    $amount          = 'Rp ' . number_format((int) $payload['gross_amount'], 0, ',', '.');
                    $paymentTypeName = $invoice->paymentType?->name ?? 'tagihan';
                    $this->notif->send(
                        $invoice->student->user,
                        'payment_confirmed',
                        'Pembayaran Berhasil',
                        "Pembayaran {$paymentTypeName} sebesar {$amount} via Midtrans telah dikonfirmasi.",
                        ['invoice_id' => $invoice->id]
                    );
                }
            }
        });
    }

    private function verifyMidtransSignature(array $payload): void
    {
        $serverKey = config('services.midtrans.server_key');

        $expected = hash('sha512',
            ($payload['order_id'] ?? '') .
            ($payload['status_code'] ?? '') .
            ($payload['gross_amount'] ?? '') .
            $serverKey
        );

        abort_if(
            !isset($payload['signature_key']) || !hash_equals($expected, $payload['signature_key']),
            403,
            'Invalid Midtrans signature.'
        );
    }

    public function deletePayment(Payment $payment): void
    {
        DB::transaction(function () use ($payment) {
            $invoice = $payment->invoice;

            if ($payment->proof_file) {
                Storage::disk('public')->delete($payment->proof_file);
            }

            $payment->delete();
            $this->invoiceService->recalculateStatus($invoice);
        });
    }

    public function generateReceiptData(Invoice $invoice): array
    {
        $invoice->load(['student.user', 'paymentType', 'payments.tuKeuangan']);

        // Generate receipt_code lazily jika invoice sudah ada pembayaran tapi belum punya kode
        if (!$invoice->receipt_code && $invoice->payments->isNotEmpty()) {
            $invoice->update(['receipt_code' => Str::uuid()->toString()]);
        }

        $lastCashPayment = $invoice->payments
            ->where('method', 'cash')
            ->sortByDesc('paid_at')
            ->first();

        $lastMidtransPayment = $invoice->payments
            ->where('method', 'midtrans')
            ->sortByDesc('paid_at')
            ->first();

        // School settings & logo
        $school     = \App\Models\SchoolSetting::current();
        $logoBase64 = null;
        $logoMime   = 'image/png';
        if ($school?->logo) {
            $logoPath = storage_path('app/public/' . $school->logo);
            if (file_exists($logoPath)) {
                $logoBase64 = base64_encode(file_get_contents($logoPath));
                $logoMime   = mime_content_type($logoPath) ?: 'image/png';
            }
        }

        return [
            'invoice'       => $invoice,
            'student'       => $invoice->student,
            'payment_type'  => $invoice->paymentType,
            'total_paid'    => $invoice->total_paid,
            'remaining'     => $invoice->remaining_amount,
            'status'        => $invoice->status,
            'receipt_code'  => $invoice->receipt_code,
            'verify_url'    => $invoice->receipt_code
                               ? route('receipt.verify', $invoice->receipt_code)
                               : null,
            'confirmed_by'  => $lastCashPayment?->tuKeuangan?->name,
            'is_online'     => $lastMidtransPayment !== null,
            'wali_name'     => $invoice->student?->user?->name,
            'school'        => $school,
            'logo_base64'   => $logoBase64,
            'logo_mime'     => $logoMime,
        ];
    }
}