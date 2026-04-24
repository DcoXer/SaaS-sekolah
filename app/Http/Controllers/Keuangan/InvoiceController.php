<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\Student;
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
        $activeYear = $this->academicYearService->getActive();

        return Inertia::render('Keuangan/Invoice/Index', [
            'unpaidInvoices' => $activeYear
                                ? $this->service->getUnpaidByAcademicYear($activeYear)
                                : collect(),
            'summary'        => $activeYear
                                ? $this->service->getSummaryByAcademicYear($activeYear)
                                : null,
            'activeYear'     => $activeYear,
        ]);
    }

    public function show(Student $student): Response
    {
        $activeYear = $this->academicYearService->getActive();

        abort_if(!$activeYear, 404);

        return Inertia::render('Keuangan/Invoice/Show', [
            'student'  => $student->load('classrooms'),
            'invoices' => $this->service->getByStudent($student, $activeYear)->map(function ($inv) {
                $inv->append(['remaining_amount', 'total_paid']);
                return $inv;
            }),
        ]);
    }
}