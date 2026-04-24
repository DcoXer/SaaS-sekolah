<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Services\FinancialReportService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(private FinancialReportService $service) {}

    public function export(Request $request): StreamedResponse
    {
        $academicYear = $request->filled('academic_year_id')
            ? AcademicYear::findOrFail($request->academic_year_id)
            : AcademicYear::where('status', 'active')->firstOrFail();

        $spreadsheet = $this->service->build($academicYear);

        $filename = 'Laporan-Keuangan-' . str($academicYear->name)->slug() . '.xlsx';

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control'       => 'max-age=0',
        ]);
    }
}
