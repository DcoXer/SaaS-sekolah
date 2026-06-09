<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StudentAccountsSheet implements FromArray, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    public function __construct(
        private string $sheetTitle,
        private array  $rows,
    ) {}

    public function title(): string
    {
        // Excel sheet name max 31 chars
        return mb_substr($this->sheetTitle, 0, 31);
    }

    public function headings(): array
    {
        return ['Nama Siswa', 'Nama Wali Murid', 'Email', 'Password'];
    }

    public function array(): array
    {
        return array_map(fn($c) => [
            $c['student_name'],
            $c['parent_name'],
            $c['email'],
            $c['password'],
        ], $this->rows);
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
