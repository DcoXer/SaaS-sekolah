<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Http\Requests\RequestLetterRequest;
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
        $user    = request()->user();
        $student = $user->student;

        abort_if(!$student, 404);

        return Inertia::render('Siswa/Letter/Index', [
            'letters'       => $this->service->getByStudent($student),
            'notifications' => $this->service->getNotificationsForStudent($student),
            'templates'     => $this->templateService->getAll()
                                   ->where('letterType.category', 'keterangan')
                                   ->values(),
        ]);
    }

    public function store(RequestLetterRequest $request)
    {
        $user     = request()->user();
        $student  = $user->student;
        $template = LetterTemplate::findOrFail($request->letter_template_id);

        abort_if(!$student, 404);

        $letter = $this->service->requestLetter($template, $user, $student);

        $typeName = $template->letterType->name ?? $template->name;
        $this->notif->sendToRole('operator', 'letter_request',
            'Permohonan Surat Baru',
            "{$student->name} mengajukan permohonan {$typeName}",
            ['letter_id' => $letter->id, 'student_id' => $student->id]
        );

        return redirect()->back()->with('success', 'Permohonan surat berhasil dikirim.');
    }

    public function show(Letter $letter): Response
    {
        $user    = request()->user();
        $student = $user->student;

        abort_if($letter->student_id !== $student?->id, 403);

        return Inertia::render('Siswa/Letter/Show', [
            'letter' => $letter->load(['letterTemplate.letterType', 'approvedBy']),
        ]);
    }

    public function markAsRead(Letter $letter)
    {
        $user    = request()->user();
        $student = $user->student;

        abort_if(!$student, 404);

        $this->service->markAsRead($letter, $student);

        return response()->json(['success' => true]);
    }
}