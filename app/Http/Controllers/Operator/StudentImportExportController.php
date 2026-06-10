<?php

namespace App\Http\Controllers\Operator;

use App\Exports\StudentsExport;
use App\Http\Controllers\Controller;
use App\Imports\StudentsImportPreview;
use App\Models\Student;
use App\Services\StudentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class StudentImportExportController extends Controller
{
    public function __construct(private StudentService $studentService) {}

    // ── Export ────────────────────────────────────────────────────────────────

    public function exportForm()
    {
        return Inertia::render('Operator/Student/Export');
    }

    public function doExport(Request $request)
    {
        $validated = $request->validate([
            'format' => 'required|in:xlsx,csv',
            'grade'  => 'nullable|in:1,2,3,4,5,6',
            'status' => 'nullable|in:active,alumni,mutasi',
        ]);

        $format   = $validated['format'];
        $grade    = $validated['grade'] ?? null;
        $status   = $validated['status'] ?? null;
        $filename = 'siswa.' . $format;

        $writerType = $format === 'csv'
            ? \Maatwebsite\Excel\Excel::CSV
            : \Maatwebsite\Excel\Excel::XLSX;

        return Excel::download(new StudentsExport($grade, $status), $filename, $writerType);
    }

    // ── Import ────────────────────────────────────────────────────────────────

    public function importForm()
    {
        return Inertia::render('Operator/Student/Import');
    }

    public function downloadTemplate()
    {
        // Build template in-memory as CSV
        $headers = ['NISN', 'NIK', 'Nama', 'Jenis Kelamin', 'Tempat Lahir', 'Tanggal Lahir', 'Kelas', 'Alamat', 'Nama Ayah', 'Nama Ibu', 'Nama Wali'];
        $rows    = [
            ['0136474090', '3671051912130001', 'Contoh Nama Siswa', 'Laki-laki', 'Jakarta', '2013-12-19', '6', 'Jl. Contoh No. 1', 'Budi Santoso', 'Siti Rahayu', 'Budi Santoso'],
            ['0987654321', '3671052001140002', 'Contoh Nama Siswi', 'Perempuan', 'Bandung', '2014-01-20', '5', 'Jl. Contoh No. 2', 'Ahmad Yani', 'Dewi Sari', 'Ahmad Yani'],
        ];

        $callback = function () use ($headers, $rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);
            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        };

        return response()->streamDownload($callback, 'template_siswa.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function preview(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        $path = $request->file('file')->store('imports/temp');

        $result = (new StudentsImportPreview())->preview($path);

        return Inertia::render('Operator/Student/Import', [
            'previewData' => [
                'rows'          => $result['rows'],
                'errors'        => $result['errors'],
                'existingNisns' => $result['existingNisns'],
                'tempPath'      => $path,
            ],
        ]);
    }

    public function confirm(Request $request)
    {
        $request->validate([
            'temp_path' => 'required|string',
        ]);

        $path = $request->input('temp_path');

        if (!str_starts_with($path, 'imports/temp')) {
            abort(422, 'Path tidak valid.');
        }

        if (!Storage::exists($path)) {
            return back()->withErrors(['file' => 'File sementara tidak ditemukan. Silakan upload ulang.']);
        }

        $result = (new StudentsImportPreview())->preview($path);

        if (!empty($result['errors'])) {
            return back()->withErrors(['import' => 'File mengandung ' . count($result['errors']) . ' error. Perbaiki file dan upload ulang.']);
        }

        $rows = $result['rows'];

        // ── Batch lookup: 1 query untuk semua NISN ────────────────────────────
        $nisns           = array_filter(array_column($rows, 'nisn'));
        $existingStudents = Student::whereIn('nisn', $nisns)
            ->get(['id', 'nisn'])
            ->keyBy('nisn');

        // ── Pre-generate NIS untuk siswa baru (1 query, bukan N) ──────────────
        $newCount  = collect($rows)->filter(fn($r) => !isset($existingStudents[$r['nisn']]))->count();
        $nisPrefix = now()->format('Ym');
        $lastNis   = Student::where('nis', 'like', $nisPrefix . '%')
            ->orderByDesc('nis')
            ->value('nis');
        $nisSeq    = $lastNis ? ((int) substr($lastNis, strlen($nisPrefix)) + 1) : 1;

        $count = 0;

        DB::transaction(function () use ($rows, $existingStudents, $nisPrefix, &$nisSeq, &$count) {
            $toInsert = [];
            $now      = now();

            foreach ($rows as $row) {
                $existing = $existingStudents[$row['nisn']] ?? null;

                if ($existing) {
                    // Update langsung via PK — tidak perlu query lagi
                    Student::where('id', $existing->id)->update([
                        'name'          => $row['name'],
                        'nik'           => $row['nik'] ?: null,
                        'birth_place'   => $row['birth_place'] ?: null,
                        'birth_date'    => $row['birth_date'] ?: null,
                        'grade'         => $row['grade'],
                        'gender'        => $row['gender'],
                        'address'       => $row['address'] ?: null,
                        'father_name'   => $row['father_name'] ?: null,
                        'mother_name'   => $row['mother_name'] ?: null,
                        'guardian_name' => $row['guardian_name'] ?: null,
                    ]);
                } else {
                    // Kumpulkan untuk batch insert
                    $toInsert[] = [
                        'nisn'          => $row['nisn'],
                        'nik'           => $row['nik'] ?: null,
                        'nis'           => $nisPrefix . str_pad($nisSeq++, 3, '0', STR_PAD_LEFT),
                        'name'          => $row['name'],
                        'gender'        => $row['gender'],
                        'grade'         => $row['grade'] ?? 1,
                        'birth_place'   => $row['birth_place'] ?: null,
                        'birth_date'    => $row['birth_date'] ?: null,
                        'address'       => $row['address'] ?: null,
                        'father_name'   => $row['father_name'] ?: null,
                        'mother_name'   => $row['mother_name'] ?: null,
                        'guardian_name' => $row['guardian_name'] ?: null,
                        'status'        => 'active',
                        'created_at'    => $now,
                        'updated_at'    => $now,
                    ];
                }

                $count++;
            }

            // Batch insert semua siswa baru sekaligus (1 query)
            if (!empty($toInsert)) {
                foreach (array_chunk($toInsert, 500) as $chunk) {
                    Student::insert($chunk);
                }
            }
        });

        Storage::delete($path);

        return redirect()->route('operator.students.index')
            ->with('success', "Berhasil mengimpor {$count} data siswa.");
    }
}
