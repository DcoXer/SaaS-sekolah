<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Services\AcademicYearService;
use App\Services\InvoiceService;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceController extends Controller
{
    public function __construct(
        private InvoiceService $service,
        private AcademicYearService $academicYearService,
    ) {}

    public function index(): Response
    {
        $user       = request()->user();
        $student    = $user->student;
        $activeYear = $this->academicYearService->getActive();

        abort_if(!$student || !$activeYear, 404);

        return Inertia::render('Siswa/Invoice/Index', [
            'invoices'      => $this->service->getByStudent($student, $activeYear),
            'hasExamAccess' => $this->service->hasExamAccess($student, $activeYear),
            'activeYear'    => $activeYear,
            'midtrans'      => [
                'client_key'    => config('services.midtrans.client_key'),
                'is_production' => config('services.midtrans.is_production'),
            ],
        ]);
    }
}