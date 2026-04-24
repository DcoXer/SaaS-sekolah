<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Http\Requests\SyncPredicateConfigRequest;
use App\Models\AcademicYear;
use App\Services\AcademicYearService;
use App\Services\PredicateConfigService;
use Inertia\Inertia;
use Inertia\Response;

class PredicateConfigController extends Controller
{
    public function __construct(
        private PredicateConfigService $service,
        private AcademicYearService $academicYearService,
    ) {}

    public function index(): Response
    {
        $activeYear = $this->academicYearService->getActive();

        return Inertia::render('Operator/PredicateConfig/Index', [
            'configs'      => $activeYear
                               ? $this->service->getByAcademicYear($activeYear)
                               : collect(),
            'academicYear' => $activeYear,
        ]);
    }

    public function sync(SyncPredicateConfigRequest $request, AcademicYear $academicYear)
    {
        $this->service->sync($academicYear, $request->validated()['configs']);

        return redirect()->back()->with('success', 'Konfigurasi predikat berhasil disimpan.');
    }
}