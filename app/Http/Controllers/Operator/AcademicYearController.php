<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAcademicYearRequest;
use App\Models\AcademicYear;
use App\Services\AcademicYearService;
use Inertia\Inertia;
use Inertia\Response;

class AcademicYearController extends Controller
{
    public function __construct(private AcademicYearService $service) {}

    public function index(): Response
    {
        return Inertia::render('Operator/AcademicYear/Index', [
            'academicYears' => $this->service->getAll(),
        ]);
    }

    public function store(StoreAcademicYearRequest $request)
    {
        $this->service->create($request->validated());

        return redirect()->back()->with('success', 'Tahun ajaran berhasil diajukan, menunggu persetujuan Kamad.');
    }
}