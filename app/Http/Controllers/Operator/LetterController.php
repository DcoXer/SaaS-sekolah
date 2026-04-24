<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNotificationLetterRequest;
use App\Models\Letter;
use App\Models\LetterTemplate;
use App\Services\LetterService;
use App\Services\LetterTemplateService;
use App\Services\LetterTypeService;
use App\Services\NotificationService;
use Inertia\Inertia;
use Inertia\Response;

class LetterController extends Controller
{
    public function __construct(
        private LetterService $service,
        private LetterTemplateService $templateService,
        private LetterTypeService $letterTypeService,
        private NotificationService $notif,
    ) {}

    public function index(): Response
    {
        return Inertia::render('Operator/Letter/Index', [
            'letters'     => $this->service->getAll(),
            'letterTypes' => $this->letterTypeService->getActive(),
            'templates'   => $this->templateService->getAll(),
        ]);
    }

    public function storeNotification(StoreNotificationLetterRequest $request)
    {
        $template = LetterTemplate::findOrFail($request->letter_template_id);

        $letter = $this->service->createNotification(
            $template,
            $request->user(),
            $request->validated()
        );

        // Notifikasi in-app ke setiap siswa penerima
        $letter->load('letterRecipients.student.user', 'letterTemplate.letterType');
        $typeName = $letter->letterTemplate?->letterType?->name ?? 'Pemberitahuan';

        foreach ($letter->letterRecipients as $recipient) {
            if ($recipient->student?->user) {
                $this->notif->send(
                    $recipient->student->user,
                    'notification_letter',
                    'Pemberitahuan Baru',
                    "Anda menerima surat {$typeName} dari sekolah. Silakan cek menu Surat.",
                    ['letter_id' => $letter->id]
                );
            }
        }

        return redirect()->back()->with('success', 'Surat pemberitahuan berhasil dikirim.');
    }

    public function submitForApproval(Letter $letter)
    {
        $this->service->submitForApproval($letter);

        $letter->load('letterTemplate.letterType', 'student');
        $typeName = $letter->letterTemplate?->letterType?->name ?? 'Surat';
        $studentName = $letter->student?->name ?? 'Siswa';

        $this->notif->sendToRole('kamad', 'letter_submitted',
            'Surat Menunggu Persetujuan',
            "Surat {$typeName} atas nama {$studentName} menunggu persetujuan Anda",
            ['letter_id' => $letter->id]
        );

        return redirect()->back()->with('success', 'Surat berhasil diajukan ke Kamad.');
    }
}