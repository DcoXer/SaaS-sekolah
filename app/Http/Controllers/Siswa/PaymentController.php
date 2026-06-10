<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
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

    public function requestCash(Invoice $invoice)
    {
        $user    = request()->user();
        $student = $user->student;

        abort_if($invoice->student_id !== $student?->id, 403);
        abort_if($invoice->isPaid(), 400);

        $this->service->createCashRequest($invoice, $student);

        $this->notif->sendToRole('tu_keuangan', 'cash_request',
            'Request Pembayaran Cash',
            "{$student->name} mengajukan pembayaran cash untuk {$invoice->paymentType->name}",
            ['invoice_id' => $invoice->id, 'student_id' => $student->id]
        );

        return back()->with('success', 'Request pembayaran cash berhasil dikirim. Silakan datang ke kantor TU untuk menyelesaikan pembayaran.');
    }

    public function initiate(Invoice $invoice)
    {
        $user    = request()->user();
        $student = $user->student;

        // Pastikan invoice milik siswa ini
        abort_if($invoice->student_id !== $student?->id, 403);
        abort_if($invoice->isPaid(), 400);

        // Guard: pastikan masih ada sisa yang harus dibayar
        $remaining = $invoice->remaining_amount;
        if ($remaining <= 0) {
            return response()->json(['error' => 'Invoice sudah lunas.'], 400);
        }

        $result = $this->service->initiateMidtransPayment($invoice, $user);

        return response()->json($result);
    }

    public function finish()
    {
        $orderId    = request()->query('order_id');
        $allParams  = request()->query();

        \Illuminate\Support\Facades\Log::info('Midtrans finish redirect received', [
            'all_params' => $allParams,
            'url'        => request()->fullUrl(),
        ]);

        if (!$orderId || !preg_match('/^INV-(\d+)-\d+$/', $orderId, $m)) {
            return redirect()->route('siswa.invoices.index')
                ->with('error', 'Data pembayaran tidak valid.');
        }

        $invoice = Invoice::find((int) $m[1]);

        if (!$invoice) {
            return redirect()->route('siswa.invoices.index')
                ->with('error', 'Invoice tidak ditemukan.');
        }

        // Pastikan invoice milik siswa yang sedang login
        $student = request()->user()->student;
        if ($invoice->student_id !== $student?->id) {
            abort(403);
        }

        // Jika Midtrans tidak mengirim transaction_status → user tutup popup tanpa bayar
        $midtransStatus = request()->query('transaction_status');
        if (!$midtransStatus) {
            return redirect()->route('siswa.invoices.index')
                ->with('error', 'Pembayaran dibatalkan atau belum diselesaikan di halaman Midtrans.');
        }

        // Cek dulu di DB — kalau webhook sudah masuk, payment sudah tercatat
        $invoice->refresh();
        $paidByThisOrder = $invoice->payments()
            ->where('midtrans_order_id', $orderId)
            ->exists();

        if ($paidByThisOrder) {
            return redirect()->route('siswa.invoices.index')
                ->with('success', 'Pembayaran berhasil dikonfirmasi.');
        }

        if ($invoice->isPaid()) {
            return redirect()->route('siswa.invoices.index')
                ->with('success', 'Invoice sudah lunas.');
        }

        // Webhook belum tiba — coba query Midtrans API sebagai fallback
        try {
            $status = $this->service->verifyAndProcessMidtransPayment($invoice, $orderId);

            $message = match ($status) {
                'settlement', 'capture' => 'Pembayaran berhasil dikonfirmasi.',
                'pending'               => 'Pembayaran sedang diproses. Status akan diperbarui otomatis.',
                default                 => null,
            };

            if ($message) {
                return redirect()->route('siswa.invoices.index')->with('success', $message);
            }

            return redirect()->route('siswa.invoices.index')
                ->with('error', 'Pembayaran tidak berhasil. Silakan coba lagi.');

        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Midtrans finish verify failed', [
                'order_id' => $orderId,
                'invoice'  => $invoice->id,
                'error'    => $e->getMessage(),
            ]);

            // Jangan percayai query param dari redirect — status final datang via webhook.
            return redirect()->route('siswa.invoices.index')
                ->with('success', 'Pembayaran sedang diverifikasi. Status tagihan akan diperbarui otomatis dalam beberapa saat.');
        }
    }

    public function verify(Invoice $invoice)
    {
        $user    = request()->user();
        $student = $user->student;

        abort_if($invoice->student_id !== $student?->id, 403);

        $orderId = request()->input('order_id');
        abort_if(!$orderId, 422, 'order_id wajib diisi.');

        $status = $this->service->verifyAndProcessMidtransPayment($invoice, $orderId);

        return response()->json(['transaction_status' => $status]);
    }

    public function callback()
    {
        $payload = request()->all();
        $this->service->handleMidtransCallback($payload);

        return response()->json(['status' => 'ok']);
    }

    public function receipt(Invoice $invoice): Response
    {
        $user    = request()->user();
        $student = $user->student;

        abort_if($invoice->student_id !== $student?->id, 403);

        return Inertia::render('Siswa/Payment/Receipt', [
            'receiptData' => $this->service->generateReceiptData($invoice),
        ]);
    }

    public function receiptPdf(Invoice $invoice)
    {
        $user    = request()->user();
        $student = $user->student;

        abort_if($invoice->student_id !== $student?->id, 403);

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

        $filename = 'kwitansi-' . str($data['student']->name)->slug() . '-' . str($data['payment_type']->name)->slug() . '.pdf';

        return $pdf->download($filename);
    }
}