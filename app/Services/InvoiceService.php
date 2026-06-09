<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Invoice;
use App\Models\PaymentType;
use App\Models\Student;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    public function getByStudent(Student $student, AcademicYear $academicYear): Collection
    {
        return Invoice::with('paymentType', 'payments', 'paymentRequest')
            ->where('student_id', $student->id)
            ->where('academic_year_id', $academicYear->id)
            ->orderBy('due_date')
            ->get();
    }

    public function getUnpaidByStudent(Student $student, AcademicYear $academicYear): Collection
    {
        return Invoice::with('paymentType')
            ->where('student_id', $student->id)
            ->where('academic_year_id', $academicYear->id)
            ->whereIn('status', ['unpaid', 'partial'])
            ->orderBy('due_date')
            ->get();
    }

    public function getUnpaidByAcademicYear(AcademicYear $academicYear): Collection
    {
        return Invoice::with(['student.classrooms', 'paymentType'])
            ->where('academic_year_id', $academicYear->id)
            ->whereNotNull('student_id')
            ->whereNotNull('payment_type_id')
            ->whereIn('status', ['unpaid', 'partial'])
            ->orderBy('due_date')
            ->get();
    }

    public function generateForPaymentType(PaymentType $paymentType): void
    {
        DB::transaction(function () use ($paymentType) {
            $academicYear = $paymentType->academicYear;

            // Ambil semua siswa aktif
            $query = Student::where('status', 'active');

            // Kalau ada grade restriction
            if ($paymentType->grade) {
                $query->whereHas('classrooms', function ($q) use ($paymentType, $academicYear) {
                    $q->where('classrooms.grade', $paymentType->grade)
                        ->where('student_classrooms.academic_year_id', $academicYear->id);
                });
            }

            $students = $query->get();

            foreach ($students as $student) {
                Invoice::firstOrCreate(
                    [
                        'student_id'      => $student->id,
                        'payment_type_id' => $paymentType->id,
                    ],
                    [
                        'academic_year_id' => $academicYear->id,
                        'amount'           => $paymentType->amount,
                        'status'           => 'unpaid',
                        'due_date'         => $paymentType->due_date,
                    ]
                );
            }
        });
    }

    public function recalculateStatus(Invoice $invoice): void
    {
        // Bungkus dalam transaction + lockForUpdate agar aman dari concurrent Midtrans callback
        DB::transaction(function () use ($invoice) {
            $locked    = Invoice::lockForUpdate()->findOrFail($invoice->id);
            $totalPaid = $locked->payments()->sum('amount');

            $status = match (true) {
                $totalPaid <= 0               => 'unpaid',
                $totalPaid < $locked->amount  => 'partial',
                default                       => 'paid',
            };

            $locked->update(['status' => $status]);
        });
    }

    public function hasExamAccess(Student $student, AcademicYear $academicYear): bool
    {
        // Siswa yang sudah alumni atau non-active tidak punya akses ujian
        if ($student->status !== 'active') {
            return false;
        }

        return !Invoice::where('student_id', $student->id)
            ->where('academic_year_id', $academicYear->id)
            ->whereHas('paymentType', fn($q) => $q->where('is_exam_related', true))
            ->whereIn('status', ['unpaid', 'partial'])
            ->exists();
    }

    public function getSummaryByAcademicYear(AcademicYear $academicYear): array
    {
        $invoices = Invoice::with('paymentType')
            ->where('academic_year_id', $academicYear->id)
            ->whereNotNull('student_id')
            ->whereNotNull('payment_type_id')
            ->get();

        $totalAmount    = $invoices->sum('amount');
        $totalPaid      = $invoices->sum(fn($i) => $i->payments()->sum('amount'));
        $totalUnpaid    = $totalAmount - $totalPaid;

        // Breakdown per jenis pembayaran
        $breakdown = $invoices->groupBy('payment_type_id')->map(function ($group) {
            $paymentType    = $group->first()->paymentType;
            $totalAmount    = $group->sum('amount');
            $totalPaid      = $group->sum(fn($i) => $i->payments()->sum('amount'));

            return [
                'payment_type'  => $paymentType->name,
                'total_amount'  => $totalAmount,
                'total_paid'    => $totalPaid,
                'total_unpaid'  => $totalAmount - $totalPaid,
                'paid_count'    => $group->where('status', 'paid')->count(),
                'unpaid_count'  => $group->whereIn('status', ['unpaid', 'partial'])->count(),
            ];
        })->values();

        return [
            'total_amount'  => $totalAmount,
            'total_paid'    => $totalPaid,
            'total_unpaid'  => $totalUnpaid,
            'breakdown'     => $breakdown,
        ];
    }
}
