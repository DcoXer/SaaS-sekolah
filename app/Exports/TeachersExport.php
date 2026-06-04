<?php

namespace App\Exports;

use App\Models\Teacher;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TeachersExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    public function collection()
    {
        return Teacher::with('user')->orderBy('id')->get();
    }

    public function headings(): array
    {
        return [
            'NIP',
            'Nama',
            'Jenis Kelamin',
            'No. HP',
            'Tipe',
            'Jabatan',
        ];
    }

    public function map($teacher): array
    {
        return [
            $teacher->nip,
            $teacher->user?->name ?? '',
            $teacher->gender === 'L' ? 'Laki-laki' : ($teacher->gender === 'P' ? 'Perempuan' : ''),
            $teacher->phone,
            match ($teacher->type) {
                'guru_kelas'  => 'Guru Kelas',
                'guru_bidang' => 'Guru Bidang',
                default       => $teacher->type,
            },
            $teacher->position ?? '',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
