<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Inertia\Inertia;
use Inertia\Response;

class ReceiptVerifyController extends Controller
{
    public function show(string $code): Response
    {
        $invoice = Invoice::with(['student', 'paymentType', 'payments.tuKeuangan'])
            ->where('receipt_code', $code)
            ->first();

        if (!$invoice) {
            return Inertia::render('Receipt/Verify', [
                'valid'   => false,
                'invoice' => null,
            ]);
        }

        $lastCashPayment = $invoice->payments
            ->where('method', 'cash')
            ->sortByDesc('paid_at')
            ->first();

        $totalPaid = $invoice->payments->sum('amount');

        return Inertia::render('Receipt/Verify', [
            'valid'        => true,
            'invoice'      => $invoice,
            'student'      => $invoice->student,
            'payment_type' => $invoice->paymentType,
            'total_paid'   => $totalPaid,
            'remaining'    => $invoice->amount - $totalPaid,
            'status'       => $invoice->status,
            'confirmed_by' => $lastCashPayment?->tuKeuangan?->name,
            'confirmed_at' => $lastCashPayment?->paid_at?->translatedFormat('d F Y'),
        ]);
    }
}
