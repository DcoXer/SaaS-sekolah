<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use App\Http\Requests\RecordCashPaymentRequest;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\NotificationService;
use App\Services\PaymentService;
use Barryvdh\DomPDF\Facade\Pdf;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    public function __construct(
        private PaymentService $service,
        private NotificationService $notif,
    ) {}

    public function store(RecordCashPaymentRequest $request, Invoice $invoice)
    {
        // Validasi amount tidak melebihi sisa tagihan
        $remaining = $invoice->remaining_amount;

        if ($remaining <= 0) {
            return redirect()->back()->withErrors([
                'amount' => 'Invoice ini sudah lunas.',
            ]);
        }

        if ($request->amount > $remaining) {
            return redirect()->back()->withErrors([
                'amount' => "Nominal pembayaran melebihi sisa tagihan (Rp " . number_format($remaining) . ").",
            ]);
        }

        $this->service->recordCashPayment(
            $invoice,
            $request->user(),
            $request->validated()
        );

        $invoice->load('student.user', 'paymentType');
        if ($invoice->student?->user) {
            $amountFmt       = 'Rp ' . number_format($request->amount, 0, ',', '.');
            $paymentTypeName = $invoice->paymentType?->name ?? 'tagihan';
            $this->notif->send(
                $invoice->student->user,
                'payment_recorded',
                'Pembayaran Dikonfirmasi',
                "Pembayaran {$paymentTypeName} sebesar {$amountFmt} telah dikonfirmasi oleh TU",
                ['invoice_id' => $invoice->id]
            );
        }

        return redirect()->back()->with('success', 'Pembayaran berhasil dicatat.');
    }

    public function destroy(Payment $payment)
    {
        $this->service->deletePayment($payment);

        return redirect()->back()->with('success', 'Pembayaran berhasil dihapus.');
    }

    public function receipt(Invoice $invoice): Response
    {
        return Inertia::render('Keuangan/Payment/Receipt', [
            'receiptData' => $this->service->generateReceiptData($invoice),
        ]);
    }

    public function receiptPdf(Invoice $invoice)
    {
        $data = $this->service->generateReceiptData($invoice);

        $qrPng = $data['verify_url']
            ? \App\Helpers\QrCodeHelper::pngBase64($data['verify_url'])
            : null;

        $pdf = Pdf::loadView('pdf.receipt', [
            'invoice'       => $data['invoice'],
            'student'       => $data['student'],
            'payment_type'  => $data['payment_type'],
            'total_paid'    => $data['total_paid'],
            'remaining'     => $data['remaining'],
            'status'        => $data['status'],
            'qr_png'        => $qrPng,
            'confirmed_by'  => $data['confirmed_by'],
            'is_online'     => $data['is_online'],
            'wali_name'     => $data['wali_name'],
            'school'        => $data['school'],
            'logo_base64'   => $data['logo_base64'],
            'logo_mime'     => $data['logo_mime'],
        ])->setPaper('a5', 'portrait');

        $studentSlug     = $data['student'] ? str($data['student']->name)->slug() : 'siswa';
        $paymentTypeSlug = $data['payment_type'] ? str($data['payment_type']->name)->slug() : 'pembayaran';
        $filename = 'kwitansi-' . $studentSlug . '-' . $paymentTypeSlug . '.pdf';

        return $pdf->download($filename);
    }
}