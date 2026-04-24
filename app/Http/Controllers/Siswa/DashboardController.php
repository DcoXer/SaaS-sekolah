<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $student    = $request->user()->student;
        $activeYear = AcademicYear::where('status', 'active')->first();

        $unpaidInvoices = collect();

        if ($student) {
            $unpaidInvoices = Invoice::where('student_id', $student->id)
                ->whereIn('status', ['unpaid', 'partial'])
                ->with('paymentType')
                ->orderBy('due_date')
                ->get()
                ->map(fn ($inv) => [
                    'id'           => $inv->id,
                    'amount'       => $inv->amount,
                    'status'       => $inv->status,
                    'due_date'     => $inv->due_date?->translatedFormat('d M Y'),
                    'payment_type' => $inv->paymentType->name ?? '-',
                ]);
        }

        return Inertia::render('Siswa/Dashboard', [
            'activeYear' => $activeYear?->name,
            'student'    => $student ? ['name' => $student->name, 'nis' => $student->nis] : null,
            'stats' => [
                'unpaid'  => $unpaidInvoices->where('status', 'unpaid')->count(),
                'partial' => $unpaidInvoices->where('status', 'partial')->count(),
            ],
            'unpaidInvoices' => $unpaidInvoices,
        ]);
    }
}