<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLetterTemplateRequest;
use App\Http\Requests\UpdateLetterTemplateRequest;
use App\Models\LetterTemplate;
use App\Services\LetterTemplateService;
use App\Services\LetterTypeService;
use Inertia\Inertia;
use Inertia\Response;

class LetterTemplateController extends Controller
{
    public function __construct(
        private LetterTemplateService $service,
        private LetterTypeService $letterTypeService,
    ) {}

    public function index(): Response
    {
        return Inertia::render('Operator/LetterTemplate/Index', [
            'templates'            => $this->service->getAll(),
            'letterTypes'          => $this->letterTypeService->getActive(),
            'availablePlaceholders'=> LetterTemplateService::AVAILABLE_PLACEHOLDERS,
        ]);
    }

    public function store(StoreLetterTemplateRequest $request)
    {
        $this->service->create($request->validated());

        return redirect()->back()->with('success', 'Template surat berhasil ditambahkan.');
    }

    public function update(UpdateLetterTemplateRequest $request, LetterTemplate $letterTemplate)
    {
        $this->service->update($letterTemplate, $request->validated());

        return redirect()->back()->with('success', 'Template surat berhasil diupdate.');
    }

    public function destroy(LetterTemplate $letterTemplate)
    {
        $this->service->delete($letterTemplate);

        return redirect()->back()->with('success', 'Template surat berhasil dihapus.');
    }
}