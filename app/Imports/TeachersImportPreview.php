<?php

namespace App\Imports;

use App\Models\Teacher;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;

class TeachersImportPreview
{
    /**
     * Parse the uploaded file and return preview rows + validation errors.
     *
     * @return array{rows: array, errors: array, existingNips: array}
     */
    public function preview(string $path, string $disk = 'local'): array
    {
        $fullPath = Storage::disk($disk)->path($path);
        $ext      = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

        $rawRows = $ext === 'csv'
            ? $this->parseCsv($fullPath)
            : $this->parseXlsx($fullPath);

        if (empty($rawRows)) {
            return ['rows' => [], 'errors' => [['row' => 0, 'field' => 'file', 'message' => 'File kosong atau format tidak dikenali']], 'existingNips' => []];
        }

        // First row = headers
        $headers = array_shift($rawRows);
        $colMap  = $this->buildColumnMap($headers);

        $rows   = [];
        $errors = [];

        foreach ($rawRows as $i => $raw) {
            $rowNum = $i + 2;
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

        // Collect existing NIPs
        $nips = array_filter(array_column($rows, 'nip'));
        $existingNips = Teacher::whereIn('nip', $nips)->pluck('nip')->toArray();

        return compact('rows', 'errors', 'existingNips');
    }

    private function parseXlsx(string $path): array
    {
        $spreadsheet = IOFactory::load($path);
        $sheet       = $spreadsheet->getActiveSheet();
        $rows        = [];

        foreach ($sheet->getRowIterator() as $row) {
            $cells = [];
            foreach ($row->getCellIterator() as $cell) {
                $val = $cell->getFormattedValue();
                $val = ltrim((string) $val, "'");
                $cells[] = $val;
            }
            $rows[] = $cells;
        }

        return $rows;
    }

    private function parseCsv(string $path): array
    {
        $rows   = [];
        $handle = fopen($path, 'r');
        if ($handle === false) return [];

        while (($data = fgetcsv($handle, 0, ',')) !== false) {
            $rows[] = array_map(fn($v) => ltrim((string) $v, "'"), $data);
        }

        fclose($handle);
        return $rows;
    }

    private function buildColumnMap(array $headers): array
    {
        $map = [];

        $aliases = [
            'nip'     => ['nip'],
            'name'    => ['nama'],
            'gender'  => ['jenis_kelamin', 'jenis kelamin'],
            'phone'   => ['no_hp', 'no. hp', 'no hp', 'phone'],
            'type'    => ['tipe'],
            'position'=> ['jabatan'],
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

        $gender = $get('gender');
        $gender = match (strtolower($gender)) {
            'laki-laki', 'laki laki', 'l' => 'L',
            'perempuan', 'p'              => 'P',
            default                       => $gender,
        };

        $type = $get('type');
        $type = match (strtolower($type)) {
            'guru kelas', 'guru_kelas'   => 'guru_kelas',
            'guru bidang', 'guru_bidang' => 'guru_bidang',
            default                      => $type,
        };

        return [
            'nip'      => $get('nip'),
            'name'     => $get('name'),
            'gender'   => $gender,
            'phone'    => $get('phone'),
            'type'     => $type,
            'position' => $get('position'),
        ];
    }

    private function validateRow(array $row, int $rowNum): array
    {
        $errors = [];

        if (empty($row['name'])) {
            $errors[] = ['row' => $rowNum, 'field' => 'name', 'message' => "Baris {$rowNum}: Nama wajib diisi"];
        }

        if (!empty($row['type']) && !in_array($row['type'], ['guru_kelas', 'guru_bidang'], true)) {
            $errors[] = ['row' => $rowNum, 'field' => 'type', 'message' => "Baris {$rowNum}: Tipe harus 'Guru Kelas' atau 'Guru Bidang'"];
        }

        if (!empty($row['gender']) && !in_array($row['gender'], ['L', 'P'], true)) {
            $errors[] = ['row' => $rowNum, 'field' => 'gender', 'message' => "Baris {$rowNum}: Jenis kelamin harus Laki-laki atau Perempuan"];
        }

        return $errors;
    }

    private function isEmptyRow(array $raw): bool
    {
        return count(array_filter($raw, fn($v) => trim((string) $v) !== '')) === 0;
    }
}
