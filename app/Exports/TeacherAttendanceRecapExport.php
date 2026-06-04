<?php

namespace App\Exports;

use App\Services\TeacherAttendanceService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TeacherAttendanceRecapExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithTitle
{
    public function __construct(
        private TeacherAttendanceService $service,
        private int $month,
        private int $year,
    ) {}

    public function collection()
    {
        return $this->service->getAllMonthlyRecap($this->month, $this->year);
    }

    public function headings(): array
    {
        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        return [
            'No',
            'NIP',
            'Nama Guru',
            'Tipe',
            'Jabatan',
            'Hadir',
            'Izin',
            'Sakit',
            'Alpha',
            'Total',
            '% Kehadiran',
            'Periode',
        ];
    }

    private int $rowNo = 0;

    public function map($row): array
    {
        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        $this->rowNo++;

        $pct = $row['total'] > 0
            ? round(($row['hadir'] / $row['total']) * 100, 1) . '%'
            : '-';

        return [
            $this->rowNo,
            $row['nip'] ?? '-',
            $row['name'],
            match ($row['type']) {
                'guru_kelas'  => 'Guru Kelas',
                'guru_bidang' => 'Guru Bidang',
                default       => $row['type'],
            },
            $row['position'] ?? '-',
            $row['hadir'],
            $row['izin'],
            $row['sakit'],
            $row['alpha'],
            $row['total'],
            $pct,
            ($monthNames[$this->month] ?? $this->month) . ' ' . $this->year,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function title(): string
    {
        $monthNames = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
            9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
        ];

        return 'Rekap ' . ($monthNames[$this->month] ?? $this->month) . ' ' . $this->year;
    }
}
