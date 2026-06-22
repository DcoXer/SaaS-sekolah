<?php

namespace App\Jobs;

use App\Models\Invoice;
use App\Services\WhatsAppService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendSppReminderJob implements ShouldQueue
{
    use Queueable;

    public int $tries   = 2;
    public int $timeout = 30;

    public function __construct(
        public readonly int $invoiceId,
    ) {}

    public function handle(WhatsAppService $whatsApp): void
    {
        $invoice = Invoice::with(['student', 'paymentType'])->find($this->invoiceId);

        if (!$invoice) return;

        $phone = $invoice->student?->parent_phone ?? null;
        if (empty($phone)) {
            Log::info("SendSppReminderJob: skip invoice {$this->invoiceId}, no parent_phone");
            return;
        }

        $student     = $invoice->student;
        $paymentType = $invoice->paymentType;

        $fmt       = fn($n) => 'Rp ' . number_format($n ?? 0, 0, ',', '.');
        $totalPaid = $invoice->total_paid;
        $remaining = $invoice->remaining_amount;
        $wali      = $student->guardian_name ?: ($student->father_name ?? $student->mother_name ?? $student->name);

        $message = "Assalamu'alaikum, {$wali}.\n\n"
            . "Ini pengingat tagihan *{$paymentType->name}* untuk {$student->name}.\n\n"
            . "Tagihan      : {$fmt($invoice->amount)}\n"
            . "Sudah dibayar: {$fmt($totalPaid)}\n"
            . "Sisa         : *{$fmt($remaining)}*\n\n"
            . "Mohon segera diselesaikan. Terima kasih 🙏";

        $result = $whatsApp->sendText($phone, $message);

        if (!$result['success']) {
            Log::error("SendSppReminderJob: failed for invoice {$this->invoiceId} — {$result['message']}");
            $this->fail($result['message']);
        }
    }
}
