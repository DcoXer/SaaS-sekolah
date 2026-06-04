<?php

namespace App\Jobs;

use App\Models\SchoolSetting;
use App\Models\TeacherHonorarium;
use App\Services\WhatsAppService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SendHonorariumSlipJob implements ShouldQueue
{
    use Queueable;

    public int $tries   = 2;
    public int $timeout = 60;

    public function __construct(
        public readonly int $honorariumId,
    ) {}

    public function handle(WhatsAppService $whatsApp): void
    {
        $honorarium = TeacherHonorarium::with(['teacher.user', 'academicYear'])->find($this->honorariumId);

        if (!$honorarium) return;

        $phone = $honorarium->teacher->phone ?? null;
        if (empty($phone)) {
            Log::info("SendHonorariumSlipJob: skip honorarium {$this->honorariumId}, no phone");
            return;
        }

        $school     = SchoolSetting::first();
        $logoBase64 = null;
        $logoMime   = null;

        if ($school?->logo) {
            $path = storage_path('app/public/' . $school->logo);
            if (file_exists($path)) {
                $logoBase64 = base64_encode(file_get_contents($path));
                $logoMime   = mime_content_type($path);
            }
        }

        $pdf = Pdf::loadView('pdf.honorarium_slip', [
            'honorarium'   => $honorarium,
            'school'       => $school,
            'logo_base64'  => $logoBase64,
            'logo_mime'    => $logoMime,
            'period_label' => $honorarium->periodLabel(),
        ])->setPaper('a5', 'portrait');

        $tempFilename = 'temp/slip-honor-' . Str::uuid() . '.pdf';
        Storage::disk('public')->put($tempFilename, $pdf->output());

        $fileUrl  = Storage::disk('public')->url($tempFilename);
        $filename = 'slip-honor-' . str($honorarium->teacher->user->name)->slug()
            . '-' . $honorarium->period_month . '-' . $honorarium->period_year . '.pdf';

        $fmt     = fn($n) => 'Rp ' . number_format($n, 0, ',', '.');
        $message = "Assalamu'alaikum, {$honorarium->teacher->user->name}.\n\n"
            . "Berikut slip honor Anda periode *{$honorarium->periodLabel()}*.\n\n"
            . "• Jam Pelajaran : {$fmt($honorarium->teaching_hours_amount)} ({$honorarium->teaching_hours} jam)\n"
            . "• Transport     : {$fmt($honorarium->transport_amount)} ({$honorarium->transport_days} hari)\n"
            . "• *Total Honor  : {$fmt($honorarium->total_amount)}*\n\n"
            . "Terima kasih 🙏";

        $result = $whatsApp->sendDocument($phone, $message, $fileUrl, $filename);

        Storage::disk('public')->delete($tempFilename);

        if (!$result['success']) {
            Log::error("SendHonorariumSlipJob: failed for honorarium {$this->honorariumId} — {$result['message']}");
            $this->fail($result['message']);
        }
    }
}
