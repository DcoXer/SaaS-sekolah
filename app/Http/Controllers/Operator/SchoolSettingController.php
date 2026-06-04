<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveSchoolSettingRequest;
use App\Models\SchoolHeroPhoto;
use App\Services\SchoolSettingService;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class SchoolSettingController extends Controller
{
    public function __construct(private SchoolSettingService $service) {}

    public function index(): Response
    {
        return Inertia::render('Operator/SchoolSetting/Index', [
            'setting'    => $this->service->get(),
            'heroPhotos' => SchoolHeroPhoto::orderBy('page')->orderBy('order')->orderBy('id')->get()
                ->groupBy('page')
                ->map(fn ($photos) => $photos->map(fn ($p) => [
                    'id'       => $p->id,
                    'file_url' => Storage::disk('public')->url($p->file_path),
                    'order'    => $p->order,
                ])),
        ]);
    }

    public function save(SaveSchoolSettingRequest $request)
    {
        $this->service->save($request->validated());

        return redirect()->back()->with('success', 'Pengaturan sekolah berhasil disimpan.');
    }
}