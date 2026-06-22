<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Invoice;
use App\Models\Payment;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $recentPayments = Payment::with(['invoice.student', 'invoice.paymentType'])
            ->latest()
            ->take(5)
            ->get()
            ->map(fn ($p) => [
                'id'           => $p->id,
                'amount'       => $p->amount,
                'method'       => $p->method,
                'paid_at'      => $p->paid_at?->translatedFormat('d M Y'),
                'student_name' => $p->invoice->student->name ?? '-',
                'payment_type' => $p->invoice->paymentType->name ?? '-',
            ]);

        $monthlyPayments = collect(range(5, 0))->map(function ($i) {
            $month = now()->subMonths($i);
            $total = Payment::whereNotNull('paid_at')
                ->whereYear('paid_at', $month->year)
                ->whereMonth('paid_at', $month->month)
                ->sum('amount');
            return [
                'label' => $month->translatedFormat('M'),
                'total' => (int) $total,
            ];
        });

        $academicYears = AcademicYear::orderByDesc('id')
            ->get()
            ->map(fn ($y) => ['id' => $y->id, 'name' => $y->name, 'status' => $y->status]);

        $activeYearId = AcademicYear::where('status', 'active')->value('id');

        return Inertia::render('Keuangan/Dashboard', [
            'stats' => [
                'unpaid'       => Invoice::where('status', 'unpaid')->where('academic_year_id', $activeYearId)->count(),
                'partial'      => Invoice::where('status', 'partial')->where('academic_year_id', $activeYearId)->count(),
                'paid'         => Invoice::where('status', 'paid')->where('academic_year_id', $activeYearId)->count(),
                'total_amount' => (int) Payment::sum('amount'),
            ],
            'monthlyPayments' => $monthlyPayments,
            'recentPayments'  => $recentPayments,
            'academicYears'   => $academicYears,
        ]);
    }
}
