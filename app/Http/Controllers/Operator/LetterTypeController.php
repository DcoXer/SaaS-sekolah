<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLetterTypeRequest;
use App\Http\Requests\UpdateLetterTypeRequest;
use App\Models\LetterType;
use App\Services\LetterTypeService;
use Inertia\Inertia;
use Inertia\Response;

class LetterTypeController extends Controller
{
    public function __construct(private LetterTypeService $service) {}

    public function index(): Response
    {
        return Inertia::render('Operator/LetterType/Index', [
            'letterTypes' => $this->service->getAll(),
        ]);
    }

    public function store(StoreLetterTypeRequest $request)
    {
        $this->service->create($request->validated());

        return redirect()->back()->with('success', 'Jenis surat berhasil ditambahkan.');
    }

    public function update(UpdateLetterTypeRequest $request, LetterType $letterType)
    {
        $this->service->update($letterType, $request->validated());

        return redirect()->back()->with('success', 'Jenis surat berhasil diupdate.');
    }

    public function destroy(LetterType $letterType)
    {
        $this->service->delete($letterType);

        return redirect()->back()->with('success', 'Jenis surat berhasil dihapus.');
    }
}