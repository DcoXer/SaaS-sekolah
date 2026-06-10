<?php

namespace App\Http\Controllers\Operator;

use App\Exports\TeachersExport;
use App\Http\Controllers\Controller;
use App\Imports\TeachersImportPreview;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class TeacherImportExportController extends Controller
{
    // ── Export ────────────────────────────────────────────────────────────────

    public function exportForm()
    {
        return Inertia::render('Operator/Teacher/Export');
    }

    public function doExport(Request $request)
    {
        $validated = $request->validate([
            'format' => 'required|in:xlsx,csv',
        ]);

        $format   = $validated['format'];
        $filename = 'guru.' . $format;

        $writerType = $format === 'csv'
            ? \Maatwebsite\Excel\Excel::CSV
            : \Maatwebsite\Excel\Excel::XLSX;

        return Excel::download(new TeachersExport(), $filename, $writerType);
    }

    // ── Import ────────────────────────────────────────────────────────────────

    public function importForm()
    {
        return Inertia::render('Operator/Teacher/Import');
    }

    public function downloadTemplate()
    {
        $headers = ['NIP', 'Nama', 'Jenis Kelamin', 'No. HP', 'Tipe', 'Jabatan'];
        $rows    = [
            ['198501012010011001', 'Contoh Nama Guru', 'Laki-laki', '08123456789', 'Guru Kelas', ''],
            ['198602022010012002', 'Contoh Nama Guru 2', 'Perempuan', '08987654321', 'Guru Bidang', 'Wakamad Kurikulum'],
        ];

        $callback = function () use ($headers, $rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);
            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        };

        return response()->streamDownload($callback, 'template_guru.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function preview(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        $path = $request->file('file')->store('imports/temp');

        $result = (new TeachersImportPreview())->preview($path);

        return Inertia::render('Operator/Teacher/Import', [
            'previewData' => [
                'rows'        => $result['rows'],
                'errors'      => $result['errors'],
                'existingNips' => $result['existingNips'],
                'tempPath'    => $path,
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

        $result = (new TeachersImportPreview())->preview($path);

        if (!empty($result['errors'])) {
            return back()->withErrors(['import' => 'File mengandung ' . count($result['errors']) . ' error. Perbaiki file dan upload ulang.']);
        }

        $count = 0;

        $rows = $result['rows'];

        // ── Batch lookup: 1 query untuk semua NIP ─────────────────────────────
        $nips            = array_filter(array_column($rows, 'nip'));
        $existingTeachers = Teacher::whereIn('nip', $nips)
            ->with('user')
            ->get()
            ->keyBy('nip');

        // ── Pre-generate email slugs unik (1 query) ───────────────────────────
        // Ambil semua email @sekolah.local yang sudah ada agar tidak ada collision
        $takenEmails = User::where('email', 'like', '%@sekolah.local')
            ->pluck('email')
            ->flip(); // flip → key = email, value = index (untuk isset O(1))

        DB::transaction(function () use ($rows, $existingTeachers, &$takenEmails, &$count) {
            foreach ($rows as $row) {
                $existing = !empty($row['nip']) ? ($existingTeachers[$row['nip']] ?? null) : null;

                if ($existing) {
                    $existing->update([
                        'gender'   => $row['gender'] ?: null,
                        'phone'    => $row['phone'] ?: null,
                        'type'     => $row['type'] ?: $existing->type,
                        'position' => $row['position'] ?: null,
                    ]);
                    if (!empty($row['name']) && $existing->user) {
                        $existing->user->update(['name' => $row['name']]);
                    }
                } else {
                    // Generate email unik tanpa query per row
                    $nipSlug = $row['nip'] ? Str::slug($row['nip']) : Str::random(8);
                    $email   = $nipSlug . '@sekolah.local';
                    $suffix  = 1;
                    while (isset($takenEmails[$email])) {
                        $email = $nipSlug . $suffix++ . '@sekolah.local';
                    }
                    $takenEmails[$email] = true;

                    $user = User::create([
                        'name'     => $row['name'],
                        'email'    => $email,
                        'password' => Hash::make(Str::password(12)),
                    ]);
                    $user->assignRole('guru');

                    Teacher::create([
                        'user_id'  => $user->id,
                        'nip'      => $row['nip'] ?: null,
                        'type'     => $row['type'] ?: 'guru_kelas',
                        'gender'   => $row['gender'] ?: null,
                        'phone'    => $row['phone'] ?: null,
                        'position' => $row['position'] ?: null,
                    ]);
                }

                $count++;
            }
        });

        Storage::delete($path);

        return redirect()->route('operator.teachers.index')
            ->with('success', "Berhasil mengimpor {$count} data guru.");
    }
}
