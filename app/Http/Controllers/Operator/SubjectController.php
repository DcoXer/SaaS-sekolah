<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSubjectRequest;
use App\Http\Requests\UpdateSubjectRequest;
use App\Models\Subject;
use App\Services\SubjectService;
use Inertia\Inertia;
use Inertia\Response;

class SubjectController extends Controller
{
    public function __construct(private SubjectService $service) {}

    public function index(): Response
    {
        return Inertia::render('Operator/Subject/Index', [
            'subjects' => $this->service->getAll(),
        ]);
    }

    public function store(StoreSubjectRequest $request)
    {
        $this->service->create($request->validated());

        return redirect()->back()->with('success', 'Mata pelajaran berhasil ditambahkan.');
    }

    public function show(Subject $subject): Response
    {
        return Inertia::render('Operator/Subject/Show', [
            'subject' => $this->service->getById($subject),
        ]);
    }

    public function update(UpdateSubjectRequest $request, Subject $subject)
    {
        $this->service->update($subject, $request->validated());

        return redirect()->back()->with('success', 'Mata pelajaran berhasil diupdate.');
    }

    public function destroy(Subject $subject)
    {
        $this->service->delete($subject);

        return redirect()->back()->with('success', 'Mata pelajaran berhasil dihapus.');
    }
}