<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use App\Jobs\SendSppReminderJob;
use App\Models\Invoice;
use App\Models\PaymentType;
use App\Models\Student;
use App\Services\AcademicYearService;
use App\Services\InvoiceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
            'paymentTypes'   => PaymentType::where('cycle', 'monthly')->where('is_active', true)->get(['id', 'name', 'grade']),
        ]);
    }

    public function sendSppReminders(Request $request): RedirectResponse
    {
        $request->validate([
            'payment_type_id' => ['required', 'exists:payment_types,id'],
            'grade'           => ['nullable', 'integer', 'between:1,6'],
        ]);

        $query = Invoice::with('student')
            ->where('payment_type_id', $request->payment_type_id)
            ->whereIn('status', ['unpaid', 'partial']);

        if ($request->filled('grade')) {
            $query->whereHas('student', fn($q) => $q->where('grade', $request->grade));
        }

        $invoices = $query->get();
        $queued   = 0;

        foreach ($invoices as $index => $invoice) {
            if (empty($invoice->student?->parent_phone)) continue;

            SendSppReminderJob::dispatch($invoice->id)
                ->delay(now()->addSeconds($index * 60));

            $queued++;
        }

        return redirect()->back()->with(
            'success',
            "Reminder dijadwalkan untuk {$queued} siswa. Pesan akan dikirim bertahap."
        );
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