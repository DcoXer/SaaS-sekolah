<?php

namespace App\Exports;

use App\Exports\Sheets\PaymentTypeSheet;
use App\Exports\Sheets\SummarySheet;
use App\Models\AcademicYear;
use App\Models\Invoice;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class FinancialReportExport implements WithMultipleSheets
{
    public function __construct(
        private AcademicYear $academicYear
    ) {}

    public function sheets(): array
    {
        // Load all invoices for this academic year, with student + classroom for this year
        $invoices = Invoice::with([
                'paymentType',
                'payments',
                'student',
                'student.classrooms' => fn ($q) => $q->where('academic_year_id', $this->academicYear->id),
            ])
            ->where('academic_year_id', $this->academicYear->id)
            ->get();

        // Group by payment type
        $grouped = $invoices->groupBy('payment_type_id');

        // ── Build summary rows ───────────────────────────────────────────
        $summaryRows  = [];
        $totalInvoices = 0;
        $grandAmount   = 0;
        $grandPaid     = 0;
        $grandSisa     = 0;

        foreach ($grouped as $paymentTypeId => $group) {
            $type        = $group->first()->paymentType;
            $totalAmount = $group->sum('amount');
            $totalPaid   = $group->sum(fn ($inv) => $inv->payments->sum('amount'));
            $totalSisa   = $totalAmount - $totalPaid;
            $pct         = $totalAmount > 0 ? round($totalPaid / $totalAmount * 100, 1) : 0;

            $summaryRows[] = [
                'name'         => $type->name,
                'cycle'        => $type->cycle,
                'invoiceCount' => $group->count(),
                'totalAmount'  => $totalAmount,
                'totalPaid'    => $totalPaid,
                'totalSisa'    => $totalSisa,
                'pctLunas'     => $pct,
            ];

            $totalInvoices += $group->count();
            $grandAmount   += $totalAmount;
            $grandPaid     += $totalPaid;
            $grandSisa     += $totalSisa;
        }

        // ── Build per-type invoice rows ──────────────────────────────────
        $sheets = [];

        $sheets[] = new SummarySheet(
            academicYearName: $this->academicYear->name,
            rows: $summaryRows,
            totals: [
                'invoiceCount' => $totalInvoices,
                'totalAmount'  => $grandAmount,
                'totalPaid'    => $grandPaid,
                'totalSisa'    => $grandSisa,
            ],
        );

        foreach ($grouped as $paymentTypeId => $group) {
            $type = $group->first()->paymentType;

            // Re-load payments per invoice efficiently
            $invoiceRows = $group->sortBy('student.name')->values()->map(function ($inv, $i) {
                $paid      = $inv->payments->sum('amount');
                $classroom = $inv->student->classrooms->first()?->name ?? '-';

                return [
                    'no'        => $i + 1,
                    'nis'       => $inv->student->nis ?? '-',
                    'name'      => $inv->student->name ?? '-',
                    'classroom' => $classroom,
                    'amount'    => (int) $inv->amount,
                    'paid'      => (int) $paid,
                    'sisa'      => (int) ($inv->amount - $paid),
                    'status'    => $inv->status,
                ];
            })->values()->toArray();

            $sheets[] = new PaymentTypeSheet(
                typeName: $type->name,
                cycle: $type->cycle,
                academicYearName: $this->academicYear->name,
                invoices: $invoiceRows,
            );
        }

        return $sheets;
    }
}
