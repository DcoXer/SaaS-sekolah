<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExtracurricularRequest;
use App\Http\Requests\UpdateExtracurricularRequest;
use App\Http\Requests\StoreExtracurricularAchievementRequest;
use App\Http\Requests\UpdateExtracurricularAchievementRequest;
use App\Models\Extracurricular;
use App\Models\ExtracurricularAchievement;
use App\Models\ExtracurricularPhoto;
use App\Services\ExtracurricularService;
use Illuminate\Http\Request;
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

    public function create(): Response
    {
        return Inertia::render('Operator/Extracurricular/Create');
    }

    public function edit(Extracurricular $extracurricular): Response
    {
        return Inertia::render('Operator/Extracurricular/Edit', [
            'extracurricular' => $extracurricular,
        ]);
    }

    public function show(Extracurricular $extracurricular): Response
    {
        $extracurricular->load(['achievements', 'photos']);

        return Inertia::render('Operator/Extracurricular/Show', [
            'extracurricular' => $extracurricular,
        ]);
    }

    public function store(StoreExtracurricularRequest $request)
    {
        $ekskul = $this->service->store($request->validated());

        return redirect()->route('operator.extracurriculars.show', $ekskul)->with('success', 'Ekskul berhasil ditambahkan.');
    }

    public function update(UpdateExtracurricularRequest $request, Extracurricular $extracurricular)
    {
        $this->service->update($extracurricular, $request->validated());

        return redirect()->route('operator.extracurriculars.show', $extracurricular)->with('success', 'Ekskul berhasil diperbarui.');
    }

    public function destroy(Extracurricular $extracurricular)
    {
        $this->service->delete($extracurricular);

        return redirect()->route('operator.extracurriculars.index')->with('success', 'Ekskul berhasil dihapus.');
    }

    public function storeAchievement(StoreExtracurricularAchievementRequest $request, Extracurricular $extracurricular)
    {
        $this->service->storeAchievement($extracurricular, $request->validated());

        return redirect()->back()->with('success', 'Prestasi berhasil ditambahkan.');
    }

    public function updateAchievement(UpdateExtracurricularAchievementRequest $request, Extracurricular $extracurricular, ExtracurricularAchievement $achievement)
    {
        $this->service->updateAchievement($achievement, $request->validated());

        return redirect()->back()->with('success', 'Prestasi berhasil diperbarui.');
    }

    public function destroyAchievement(Extracurricular $extracurricular, ExtracurricularAchievement $achievement)
    {
        $this->service->deleteAchievement($achievement);

        return redirect()->back()->with('success', 'Prestasi berhasil dihapus.');
    }

    public function storePhoto(Request $request, Extracurricular $extracurricular)
    {
        $request->validate([
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
        ]);

        $this->service->storePhoto($extracurricular, $request->file('photo'));

        return redirect()->back()->with('success', 'Foto berhasil diunggah.');
    }

    public function destroyPhoto(Extracurricular $extracurricular, ExtracurricularPhoto $photo)
    {
        $this->service->deletePhoto($photo);

        return redirect()->back()->with('success', 'Foto berhasil dihapus.');
    }
}
