<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExtracurricularRequest;
use App\Http\Requests\UpdateExtracurricularRequest;
use App\Models\Extracurricular;
use App\Services\ExtracurricularService;
use Inertia\Inertia;
use Inertia\Response;

class ExtracurricularController extends Controller
{
    public function __construct(private ExtracurricularService $service) {}

    public function index(): Response
    {
        return Inertia::render('Operator/Extracurricular/Index', [
            'extracurriculars' => $this->service->all(),
        ]);
    }

    public function store(StoreExtracurricularRequest $request)
    {
        $this->service->store($request->validated());

        return redirect()->back()->with('success', 'Ekskul berhasil ditambahkan.');
    }

    public function update(UpdateExtracurricularRequest $request, Extracurricular $extracurricular)
    {
        $this->service->update($extracurricular, $request->validated());

        return redirect()->back()->with('success', 'Ekskul berhasil diperbarui.');
    }

    public function destroy(Extracurricular $extracurricular)
    {
        $this->service->delete($extracurricular);

        return redirect()->back()->with('success', 'Ekskul berhasil dihapus.');
    }
}
