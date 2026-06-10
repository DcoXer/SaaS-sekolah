<?php

namespace App\Http\Controllers;

use App\Helpers\QrCodeHelper;
use App\Models\AcademicYear;
use App\Models\Letter;
use App\Models\SchoolSetting;
use Barryvdh\DomPDF\Facade\Pdf;

class LetterPdfController extends Controller
{
    public function download(Letter $letter)
    {
        $user = request()->user();

        // Hanya role yang memiliki route ke sini yang boleh akses:
        // - siswa: hanya suratnya sendiri
        // - operator: semua surat (untuk keperluan administrasi)
        // - kamad: semua surat (untuk keperluan approval)
        abort_unless(
            $user->hasAnyRole(['siswa', 'operator', 'kamad']),
            403
        );

        if ($user->hasRole('siswa')) {
            abort_if($letter->student_id !== $user->student?->id, 403);
        }

        abort_if(!$letter->isApproved(), 403, 'Surat belum disetujui oleh Kepala Madrasah.');

        $letter->load(['letterTemplate.letterType', 'student', 'approvedBy']);

        $school = SchoolSetting::current();

        // Logo
        $logoBase64 = null;
        $logoMime   = 'image/png';
        if ($school?->logo) {
            $path = storage_path('app/public/' . $school->logo);
            if (file_exists($path)) {
                $logoBase64 = base64_encode(file_get_contents($path));
                $logoMime   = mime_content_type($path) ?: 'image/png';
            }
        }

        // Stempel
        $stampBase64 = null;
        $stampMime   = 'image/png';
        if ($school?->stamp) {
            $path = storage_path('app/public/' . $school->stamp);
            if (file_exists($path)) {
                $stampBase64 = base64_encode(file_get_contents($path));
                $stampMime   = mime_content_type($path) ?: 'image/png';
            }
        }

        // QR
        $verifyUrl = route('letters.verify', $letter->barcode_code);
        $qrPng     = QrCodeHelper::pngBase64($verifyUrl);

        // Nomor surat dari baris pertama jika ada
        $lines        = explode("\n", trim($letter->content));
        $letterNumber = '';
        if (!empty($lines[0]) && str_starts_with(trim($lines[0]), 'No.')) {
            $letterNumber = trim($lines[0]);
        }

        // Body = konten tanpa baris nomor + tanpa blok TTD di akhir
        $bodyContent = $this->extractBody($letter->content);

        $pdf = Pdf::loadView('pdf.letter', [
            'letter'        => $letter,
            'school'        => $school,
            'logo_base64'   => $logoBase64,
            'logo_mime'     => $logoMime,
            'stamp_base64'  => $stampBase64,
            'stamp_mime'    => $stampMime,
            'qr_png'        => $qrPng,
            'verify_url'    => $verifyUrl,
            'letter_number' => $letterNumber,
            'body_content'  => $bodyContent,
        ])->setPaper('a4', 'portrait');

        $typeName    = str($letter->letterTemplate?->letterType?->name ?? 'surat')->slug();
        $studentName = str($letter->student?->name ?? 'siswa')->slug();

        return $pdf->download("surat-{$typeName}-{$studentName}.pdf");
    }

    private function extractBody(string $content): string
    {
        $lines = explode("\n", $content);

        // Hapus baris pertama jika nomor surat
        $start = 0;
        if (!empty($lines[0]) && str_starts_with(trim($lines[0]), 'No.')) {
            $start = 1;
        }

        // Cari awal blok TTD dari akhir (cari baris "Kepala Madrasah" / "Kepala Sekolah")
        $ttdStart = count($lines);
        for ($i = count($lines) - 1; $i >= $start; $i--) {
            $line = trim($lines[$i]);
            if (str_contains($line, 'Kepala Madrasah') || str_contains($line, 'Kepala Sekolah')) {
                // Mundur lagi untuk cari baris tanggal di atasnya
                $j = $i - 1;
                while ($j >= $start && trim($lines[$j]) === '') {
                    $j--;
                }
                $ttdStart = ($j >= $start && preg_match('/\d{1,2}\s+\w+\s+\d{4}/', trim($lines[$j])))
                    ? $j
                    : $i;
                break;
            }
        }

        $body = array_slice($lines, $start, $ttdStart - $start);
        return rtrim(implode("\n", $body));
    }
}
