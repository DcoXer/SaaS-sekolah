<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PpdbInvoiceController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Invoice::with('ppdbRegistration')
            ->whereNotNull('ppdb_registration_id')
            ->whereNull('student_id');

        if ($request->filled('search')) {
            $q = $request->search;
            $query->whereHas('ppdbRegistration', fn($qb) =>
                $qb->where('full_name', 'like', "%{$q}%")
                   ->orWhere('registration_number', 'like', "%{$q}%")
                   ->orWhere('parent_phone', 'like', "%{$q}%")
            );
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $invoices = $query->orderBy('due_date')->paginate(20)->withQueryString();

        // Append computed attributes
        $invoices->getCollection()->each(function ($inv) {
            $inv->append(['total_paid', 'remaining_amount']);
        });

        return Inertia::render('Keuangan/PpdbInvoice/Index', [
            'invoices' => $invoices,
            'filters'  => $request->only('search', 'status'),
        ]);
    }
}
