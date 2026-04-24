<?php

namespace App\Http\Controllers\Kamad;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Services\AcademicYearService;
use Inertia\Inertia;
use Inertia\Response;

class AcademicYearController extends Controller
{
    public function __construct(private AcademicYearService $service) {}

    public function index(): Response
    {
        return Inertia::render('Kamad/AcademicYear/Index', [
            'academicYears' => $this->service->getAll(),
        ]);
    }

    public function approve(AcademicYear $academicYear)
    {
        if (!$academicYear->isPending()) {
            return redirect()->back()->with('error', 'Tahun ajaran ini tidak bisa disetujui.');
        }

        $this->service->approve($academicYear);

        return redirect()->back()->with('success', 'Tahun ajaran berhasil diaktifkan.');
    }
}