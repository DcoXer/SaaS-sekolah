<?php

namespace App\Http\Controllers\Kamad;

use App\Http\Controllers\Controller;
use App\Http\Requests\RejectLetterRequest;
use App\Models\Letter;
use App\Services\LetterService;
use App\Services\NotificationService;
use Inertia\Inertia;
use Inertia\Response;

class LetterController extends Controller
{
    public function __construct(
        private LetterService $service,
        private NotificationService $notif,
    ) {}

    public function index(): Response
    {
        return Inertia::render('Kamad/Letter/Index', [
            'waitingLetters'  => $this->service->getByStatus('waiting_approval'),
            'approvedLetters' => $this->service->getByStatus('approved'),
            'rejectedLetters' => $this->service->getByStatus('rejected'),
        ]);
    }

    public function approve(Letter $letter)
    {
        $this->service->approve($letter, request()->user());

        $letter->load('letterTemplate.letterType', 'student.user');
        $typeName = $letter->letterTemplate?->letterType?->name ?? 'Surat';
        if ($letter->student?->user) {
            $this->notif->send(
                $letter->student->user,
                'letter_approved',
                'Surat Disetujui',
                "Surat {$typeName} Anda telah disetujui oleh Kepala Madrasah",
                ['letter_id' => $letter->id]
            );
        }

        return redirect()->back()->with('success', 'Surat berhasil disetujui.');
    }

    public function reject(RejectLetterRequest $request, Letter $letter)
    {
        $this->service->reject($letter, request()->user(), $request->rejection_note);

        $letter->load('letterTemplate.letterType', 'student.user');
        $typeName = $letter->letterTemplate?->letterType?->name ?? 'Surat';
        if ($letter->student?->user) {
            $this->notif->send(
                $letter->student->user,
                'letter_rejected',
                'Surat Ditolak',
                "Surat {$typeName} Anda ditolak. Alasan: {$request->rejection_note}",
                ['letter_id' => $letter->id]
            );
        }

        return redirect()->back()->with('success', 'Surat berhasil ditolak.');
    }

    public function verify(string $barcodeCode): Response
    {
        $letter = $this->service->verifyBarcode($barcodeCode);

        return Inertia::render('Kamad/Letter/Verify', [
            'letter' => $letter,
            'valid'  => $letter !== null,
        ]);
    }
}