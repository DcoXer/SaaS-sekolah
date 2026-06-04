<?php

namespace App\Http\Controllers\Operator;

use App\Exports\TeacherAttendanceRecapExport;
use App\Http\Controllers\Controller;
use App\Services\TeacherAttendanceService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TeacherAttendanceRecapController extends Controller
{
    public function __construct(
        private TeacherAttendanceService $service,
    ) {}

    public function index(Request $request): Response
    {
        $month = (int) $request->input('month', now()->month);
        $year  = (int) $request->input('year',  now()->year);

        $recap = $this->service->getAllMonthlyRecap($month, $year);

        return Inertia::render('Operator/TeacherAttendance/Recap', [
            'recap' => $recap,
            'month' => $month,
            'year'  => $year,
        ]);
    }

    public function export(Request $request): BinaryFileResponse
    {
        $month  = (int) $request->input('month', now()->month);
        $year   = (int) $request->input('year',  now()->year);
        $format = $request->input('format', 'xlsx');

        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        $filename    = 'rekap-absensi-guru-' . ($monthNames[$month] ?? $month) . '-' . $year . '.' . $format;
        $writerType  = $format === 'csv'
            ? \Maatwebsite\Excel\Excel::CSV
            : \Maatwebsite\Excel\Excel::XLSX;

        return Excel::download(
            new TeacherAttendanceRecapExport($this->service, $month, $year),
            $filename,
            $writerType,
        );
    }
}
