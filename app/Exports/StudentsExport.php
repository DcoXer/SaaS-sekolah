<?php

namespace App\Exports;

use App\Models\Student;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StudentsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    public function __construct(
        private ?string $grade = null,
        private ?string $status = null,
    ) {}

    public function collection()
    {
        $query = Student::query()->orderBy('grade')->orderBy('name');

        if ($this->grade) {
            $query->where('grade', $this->grade);
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'NIS',
            'NISN',
            'NIK',
            'Nama',
            'Jenis Kelamin',
            'Tempat Lahir',
            'Tanggal Lahir',
            'Kelas',
            'Alamat',
            'Nama Ayah',
            'Nama Ibu',
            'Nama Wali',
            'Status',
        ];
    }

    public function map($student): array
    {
        return [
            $student->nis,
            $student->nisn,
            $student->nik,
            $student->name,
            $student->gender === 'L' ? 'Laki-laki' : ($student->gender === 'P' ? 'Perempuan' : ''),
            $student->birth_place,
            $student->birth_date ? \Carbon\Carbon::parse($student->birth_date)->format('Y-m-d') : '',
            $student->grade ? 'Kelas ' . $student->grade : '',
            $student->address,
            $student->father_name,
            $student->mother_name,
            $student->guardian_name,
            match ($student->status) {
                'active' => 'Aktif',
                'alumni' => 'Alumni',
                'mutasi' => 'Mutasi',
                default  => $student->status,
            },
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
