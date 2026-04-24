<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveSchoolSettingRequest;
use App\Services\SchoolSettingService;
use Inertia\Inertia;
use Inertia\Response;

class SchoolSettingController extends Controller
{
    public function __construct(private SchoolSettingService $service) {}

    public function index(): Response
    {
        return Inertia::render('Operator/SchoolSetting/Index', [
            'setting' => $this->service->get(),
        ]);
    }

    public function save(SaveSchoolSettingRequest $request)
    {
        $this->service->save($request->validated());

        return redirect()->back()->with('success', 'Pengaturan sekolah berhasil disimpan.');
    }
}