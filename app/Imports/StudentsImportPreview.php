<?php

namespace App\Imports;

use App\Models\Student;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;

class StudentsImportPreview
{
    /**
     * Parse the uploaded file and return preview rows + validation errors.
     *
     * @return array{rows: array, errors: array, existingNisns: array}
     */
    public function preview(string $path, string $disk = 'local'): array
    {
        $fullPath = Storage::disk($disk)->path($path);
        $ext      = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

        $rawRows = $ext === 'csv'
            ? $this->parseCsv($fullPath)
            : $this->parseXlsx($fullPath);

        if (empty($rawRows)) {
            return ['rows' => [], 'errors' => [['row' => 0, 'field' => 'file', 'message' => 'File kosong atau format tidak dikenali']], 'existingNisns' => []];
        }

        // First row = headers
        $headers = array_shift($rawRows);
        $colMap  = $this->buildColumnMap($headers);

        $rows   = [];
        $errors = [];

        foreach ($rawRows as $i => $raw) {
            $rowNum = $i + 2; // +2 because row 1 = header
            if ($this->isEmptyRow($raw)) continue;

            $row    = $this->mapRow($raw, $colMap);
            $rowErrors = $this->validateRow($row, $rowNum);

            if (!empty($rowErrors)) {
                foreach ($rowErrors as $e) {
                    $errors[] = $e;
                }
            }

            $rows[] = $row;
        }

        // Collect existing NISNs for status badges in preview
        $nisns = array_filter(array_column($rows, 'nisn'));
        $existingNisns = Student::whereIn('nisn', $nisns)->pluck('nisn')->toArray();

        return compact('rows', 'errors', 'existingNisns');
    }

    private function parseXlsx(string $path): array
    {
        // setReadDataOnly(true) skip semua styling/formula → jauh lebih cepat
        $reader = IOFactory::createReaderForFile($path);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($path);
        $sheet       = $spreadsheet->getActiveSheet();
        $rows        = [];

        foreach ($sheet->getRowIterator() as $row) {
            $cells = [];
            $iter  = $row->getCellIterator();
            $iter->setIterateOnlyExistingCells(false);
            foreach ($iter as $cell) {
                $val     = $cell->getValue();
                $val     = ltrim((string) $val, "'");
                $cells[] = trim($val);
            }
            $rows[] = $cells;
        }

        return $rows;
    }

    private function parseCsv(string $path): array
    {
        $rows = [];
        $handle = fopen($path, 'r');
        if ($handle === false) return [];

        while (($data = fgetcsv($handle, 0, ',')) !== false) {
            $rows[] = array_map(fn($v) => ltrim((string) $v, "'"), $data);
        }

        fclose($handle);
        return $rows;
    }

    /**
     * Build a map of [fieldKey => columnIndex] from header row.
     * Supports both our own template format and EMIS format.
     */
    private function buildColumnMap(array $headers): array
    {
        $map = [];

        $aliases = [
            'name'          => ['nama'],
            'nisn'          => ['nisn'],
            'nik'           => ['nik'],
            'birth_place'   => ['tempat_lahir', 'tempat lahir'],
            'birth_date'    => ['tanggal_lahir', 'tanggal lahir'],
            'grade'         => ['kelas'],
            'gender'        => ['jenis_kelamin', 'jenis kelamin'],
            'address'       => ['alamat'],
            'father_name'   => ['nama_ayah', 'nama ayah', 'nama ayah kandung'],
            'mother_name'   => ['nama_ibu', 'nama ibu', 'nama ibu kandung'],
            'guardian_name' => ['nama_wali', 'nama wali'],
        ];

        foreach ($headers as $index => $header) {
            $normalized = strtolower(trim($header));
            foreach ($aliases as $field => $candidates) {
                if (in_array($normalized, $candidates, true)) {
                    $map[$field] = $index;
                    break;
                }
            }
        }

        return $map;
    }

    private function mapRow(array $raw, array $colMap): array
    {
        $get = fn(string $field) => isset($colMap[$field]) ? trim($raw[$colMap[$field]] ?? '') : '';

        $grade = $get('grade');
        // "Kelas 6" → 6
        if (preg_match('/\d+/', $grade, $m)) {
            $grade = (int) $m[0];
        } else {
            $grade = $grade !== '' ? (int) $grade : null;
        }

        $gender = $get('gender');
        $gender = match (strtolower($gender)) {
            'laki-laki', 'laki laki', 'l' => 'L',
            'perempuan', 'p'              => 'P',
            default                       => $gender,
        };

        $birthDate = $get('birth_date');
        // Try to normalize date
        if ($birthDate) {
            try {
                $ts = strtotime($birthDate);
                $birthDate = $ts !== false ? date('Y-m-d', $ts) : $birthDate;
            } catch (\Throwable) {
                // keep original
            }
        }

        return [
            'name'          => $get('name'),
            'nisn'          => $get('nisn'),
            'nik'           => $get('nik'),
            'birth_place'   => $get('birth_place'),
            'birth_date'    => $birthDate ?: null,
            'grade'         => $grade,
            'gender'        => $gender,
            'address'       => $get('address'),
            'father_name'   => $get('father_name'),
            'mother_name'   => $get('mother_name'),
            'guardian_name' => $get('guardian_name'),
        ];
    }

    private function validateRow(array $row, int $rowNum): array
    {
        $errors = [];

        if (empty($row['name'])) {
            $errors[] = ['row' => $rowNum, 'field' => 'name', 'message' => "Baris {$rowNum}: Nama wajib diisi"];
        }

        if (empty($row['nisn'])) {
            $errors[] = ['row' => $rowNum, 'field' => 'nisn', 'message' => "Baris {$rowNum}: NISN wajib diisi"];
        } elseif (strlen($row['nisn']) > 20) {
            $errors[] = ['row' => $rowNum, 'field' => 'nisn', 'message' => "Baris {$rowNum}: NISN maksimal 20 karakter"];
        }

        if ($row['grade'] !== null && !in_array($row['grade'], [1, 2, 3, 4, 5, 6], true)) {
            $errors[] = ['row' => $rowNum, 'field' => 'grade', 'message' => "Baris {$rowNum}: Kelas harus antara 1–6"];
        }

        if (!empty($row['gender']) && !in_array($row['gender'], ['L', 'P'], true)) {
            $errors[] = ['row' => $rowNum, 'field' => 'gender', 'message' => "Baris {$rowNum}: Jenis kelamin harus Laki-laki atau Perempuan"];
        }

        if (!empty($row['birth_date'])) {
            $ts = strtotime($row['birth_date']);
            if ($ts === false) {
                $errors[] = ['row' => $rowNum, 'field' => 'birth_date', 'message' => "Baris {$rowNum}: Format tanggal lahir tidak valid"];
            }
        }

        return $errors;
    }

    private function isEmptyRow(array $raw): bool
    {
        return count(array_filter($raw, fn($v) => trim((string) $v) !== '')) === 0;
    }
}
