<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use App\Http\Requests\GenerateSppRequest;
use App\Http\Requests\StorePaymentTypeRequest;
use App\Http\Requests\UpdatePaymentTypeRequest;
use App\Models\PaymentType;
use App\Services\AcademicYearService;
use App\Services\InvoiceService;
use App\Services\PaymentTypeService;
use Inertia\Inertia;
use Inertia\Response;

class PaymentTypeController extends Controller
{
    public function __construct(
        private PaymentTypeService $service,
        private AcademicYearService $academicYearService,
        private InvoiceService $invoiceService,
    ) {}

    public function index(): Response
    {
        $activeYear = $this->academicYearService->getActive();

        return Inertia::render('Keuangan/PaymentType/Index', [
            'paymentTypes' => $activeYear
                              ? $this->service->getByAcademicYear($activeYear)
                              : collect(),
            'activeYear'   => $activeYear,
        ]);
    }

    public function store(StorePaymentTypeRequest $request)
    {
        $paymentType = $this->service->create($request->validated());

        // Auto generate invoice untuk semua siswa yang eligible
        $this->invoiceService->generateForPaymentType($paymentType);

        return redirect()->back()->with('success', 'Jenis tagihan berhasil ditambahkan dan tagihan siswa telah digenerate.');
    }

    public function update(UpdatePaymentTypeRequest $request, PaymentType $paymentType)
    {
        $this->service->update($paymentType, $request->validated());

        return redirect()->back()->with('success', 'Jenis tagihan berhasil diupdate.');
    }

    public function destroy(PaymentType $paymentType)
    {
        $this->service->delete($paymentType);

        return redirect()->back()->with('success', 'Jenis tagihan berhasil dihapus.');
    }

    public function generateSpp(GenerateSppRequest $request)
    {
        $activeYear = $this->academicYearService->getActive();

        if (!$activeYear) {
            return redirect()->back()->with('error', 'Tidak ada tahun ajaran aktif.');
        }

        // generateMonthlySpp() hanya mengembalikan tipe yang BARU dibuat
        $newSppTypes = $this->service->generateMonthlySpp($activeYear, $request->amount);

        // Generate invoice hanya untuk SPP baru — skip yang sudah ada invoicenya
        foreach ($newSppTypes as $sppType) {
            $this->invoiceService->generateForPaymentType($sppType);
        }

        return redirect()->back()->with('success', 'SPP bulanan berhasil digenerate untuk seluruh tahun ajaran.');
    }
}