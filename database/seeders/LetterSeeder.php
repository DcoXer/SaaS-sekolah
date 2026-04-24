<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Letter;
use App\Models\LetterRecipient;
use App\Models\LetterTemplate;
use App\Models\LetterType;
use App\Models\Student;
use App\Models\User;
use App\Services\LetterService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LetterSeeder extends Seeder
{
    public function __construct(private LetterService $letterService) {}

    public function run(): void
    {
        $year     = AcademicYear::where('status', 'active')->firstOrFail();
        $kamad    = User::role('kamad')->firstOrFail();
        $operator = User::role('operator')->firstOrFail();

        // ── 1. Letter Types ───────────────────────────────────────────────────
        $typeKeterangan = LetterType::create([
            'name'        => 'Surat Keterangan Aktif Siswa',
            'category'    => 'keterangan',
            'description' => 'Surat yang menyatakan siswa masih aktif bersekolah',
            'is_active'   => true,
        ]);

        $typePemberitahuan = LetterType::create([
            'name'        => 'Surat Pemberitahuan Kegiatan',
            'category'    => 'pemberitahuan',
            'description' => 'Surat pemberitahuan kepada wali murid terkait kegiatan sekolah',
            'is_active'   => true,
        ]);

        // ── 2. Letter Templates ───────────────────────────────────────────────
        $templateKeterangan = LetterTemplate::create([
            'letter_type_id'          => $typeKeterangan->id,
            'name'                    => 'Template Surat Keterangan Aktif Siswa',
            'content'                 => $this->templateKeterangan(),
            'available_placeholders'  => [
                '{{student.name}}',
                '{{student.nis}}',
                '{{classroom.name}}',
                '{{academic_year.name}}',
                '{{letter.date}}',
                '{{letter.number}}',
                '{{principal.name}}',
                '{{principal.nip}}',
                '{{school.name}}',
                '{{school.address}}',
                '{{school.phone}}',
            ],
            'is_active' => true,
        ]);

        $templatePemberitahuan = LetterTemplate::create([
            'letter_type_id'          => $typePemberitahuan->id,
            'name'                    => 'Template Surat Pemberitahuan Kegiatan Sekolah',
            'content'                 => $this->templatePemberitahuan(),
            'available_placeholders'  => [
                '{{letter.date}}',
                '{{letter.number}}',
                '{{principal.name}}',
                '{{school.name}}',
            ],
            'is_active' => true,
        ]);

        // ── 3. Letters ────────────────────────────────────────────────────────

        $rizky  = Student::where('nis', '2025001')->firstOrFail();
        $nayla  = Student::where('nis', '2025002')->firstOrFail();
        $dafa   = Student::where('nis', '2025003')->firstOrFail();
        $alya   = Student::where('nis', '2025006')->firstOrFail();

        $waliRizky = $rizky->user;
        $waliNayla = $nayla->user;
        $waliDafa  = $dafa->user;
        $waliAlya  = $alya->user;

        // Surat Rizky — approved (barcode siap)
        if ($waliRizky) {
            $suratRizky = $this->letterService->requestLetter($templateKeterangan, $waliRizky, $rizky);
            $this->letterService->submitForApproval($suratRizky);
            $this->letterService->approve($suratRizky, $kamad);
        }

        // Surat Nayla — waiting_approval (wali sudah submit, kamad belum approve)
        if ($waliNayla) {
            $suratNayla = $this->letterService->requestLetter($templateKeterangan, $waliNayla, $nayla);
            $this->letterService->submitForApproval($suratNayla);
        }

        // Surat Dafa — draft (wali baru buat, belum submit)
        if ($waliDafa) {
            $this->letterService->requestLetter($templateKeterangan, $waliDafa, $dafa);
        }

        // Surat Alya — rejected (kamad tolak dengan alasan)
        if ($waliAlya) {
            $suratAlya = $this->letterService->requestLetter($templateKeterangan, $waliAlya, $alya);
            $this->letterService->submitForApproval($suratAlya);
            $this->letterService->reject(
                $suratAlya,
                $kamad,
                'Surat tidak dapat diterbitkan karena data siswa belum lengkap. '
                . 'Mohon lengkapi data kependudukan terlebih dahulu.'
            );
        }

        // Surat Pemberitahuan — published, untuk semua siswa kelas 1
        Letter::create([
            'letter_template_id' => $templatePemberitahuan->id,
            'category'           => 'pemberitahuan',
            'requested_by'       => $operator->id,
            'target_grade'       => 1,
            'status'             => 'published',
            'content'            => $this->contentPemberitahuanPTA(),
            'published_at'       => now()->subDays(3),
        ]);

        // Generate recipients untuk surat pemberitahuan
        $suratPemberitahuan = Letter::where('category', 'pemberitahuan')->latest()->first();
        $this->letterService->generateRecipients($suratPemberitahuan);
    }

    private function templateKeterangan(): string
    {
        return <<<'TEXT'
{{letter.number}}

Bismillahirrahmanirrahim

Yang bertanda tangan di bawah ini, Kepala {{school.name}}, menerangkan bahwa:

Nama Siswa  : {{student.name}}
NIS         : {{student.nis}}
Kelas       : {{classroom.name}}
Tahun Ajaran: {{academic_year.name}}

Adalah benar-benar siswa yang AKTIF belajar di {{school.name}} yang beralamat di {{school.address}}.

Surat keterangan ini dibuat untuk keperluan yang bersangkutan dan agar dapat dipergunakan sebagaimana mestinya.

{{letter.date}}

Kepala Madrasah,



{{principal.name}}
NIP. {{principal.nip}}
TEXT;
    }

    private function templatePemberitahuan(): string
    {
        return <<<'TEXT'
{{letter.number}}

Kepada Yth.
Orang Tua / Wali Murid
Di Tempat

Assalamu'alaikum Warahmatullahi Wabarakatuh

Dengan hormat, bersama surat ini kami sampaikan pemberitahuan kepada Bapak/Ibu Orang Tua / Wali Murid {{school.name}}.

[ISI PEMBERITAHUAN]

Demikian surat pemberitahuan ini kami sampaikan, atas perhatian dan kerja sama Bapak/Ibu, kami ucapkan terima kasih.

Wassalamu'alaikum Warahmatullahi Wabarakatuh

{{letter.date}}

Kepala Madrasah,



{{principal.name}}
TEXT;
    }

    private function contentPemberitahuanPTA(): string
    {
        return <<<'TEXT'
No. 021/MI-NU/IV/2025

Kepada Yth.
Orang Tua / Wali Murid Kelas 1
Di Tempat

Assalamu'alaikum Warahmatullahi Wabarakatuh

Dengan hormat, bersama surat ini kami sampaikan pemberitahuan kepada Bapak/Ibu Orang Tua / Wali Murid MI Nurul Ulum.

Dalam rangka Pertemuan Orang Tua dan Wali (PTA) Semester Ganjil Tahun Ajaran 2025/2026, kami mengundang Bapak/Ibu untuk hadir pada:

    Hari / Tanggal  : Sabtu, 25 Oktober 2025
    Waktu           : 08.00 – 11.00 WIB
    Tempat          : Aula MI Nurul Ulum
    Acara           : Pembagian Hasil Tengah Semester & Diskusi Perkembangan Siswa

Mengingat pentingnya acara ini, kami sangat mengharapkan kehadiran Bapak/Ibu.

Demikian surat pemberitahuan ini kami sampaikan, atas perhatian dan kerja sama Bapak/Ibu, kami ucapkan terima kasih.

Wassalamu'alaikum Warahmatullahi Wabarakatuh

Probolinggo, 10 Oktober 2025

Kepala Madrasah,



Drs. H. Mahmud Syafi'i, M.Pd
TEXT;
    }
}
