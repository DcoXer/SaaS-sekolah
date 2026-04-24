<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\ReportCard;
use App\Models\ReportCardNote;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ReportCardSeeder extends Seeder
{
    /**
     * Seed raport dengan berbagai status untuk keperluan test flow:
     *
     * Kelas 1A (semester 1):
     *   - Rizky, Nayla  → approved   (guru bisa export, siswa bisa lihat)
     *   - Dafa          → waiting_approval (kamad perlu approve)
     *   - Syifa, Bagas  → draft      (guru masih input)
     *
     * Kelas 2A (semester 1):
     *   - Alya, Farhan  → approved
     *   - Keisya        → waiting_approval
     *   - Arya, Adinda  → draft
     *
     * Kelas 3A–6A: belum di-generate (Kamad perlu generate dulu)
     */
    public function run(): void
    {
        $year  = AcademicYear::where('status', 'active')->firstOrFail();
        $kamad = User::role('kamad')->firstOrFail();

        $this->seedClass($year, $kamad, '1A', [
            'Rizky Aditya Pratama'  => 'approved',
            'Nayla Putri Rahayu'    => 'approved',
            'Dafa Arkan Maulana'    => 'waiting_approval',
            'Syifa Aulia Zahra'     => 'draft',
            'Bagas Nur Hidayat'     => 'draft',
        ]);

        $this->seedClass($year, $kamad, '2A', [
            'Alya Ramadhani'  => 'approved',
            'Farhan Ramadhan' => 'approved',
            'Keisya Nur Fadilah' => 'waiting_approval',
            'Arya Wicaksono'  => 'draft',
            'Adinda Salsabila' => 'draft',
        ]);
    }

    private function seedClass(AcademicYear $year, User $kamad, string $className, array $statusMap): void
    {
        $classroom = Classroom::where('academic_year_id', $year->id)
                               ->where('name', $className)
                               ->firstOrFail();

        foreach ($statusMap as $studentName => $status) {
            $student = Student::where('name', $studentName)->firstOrFail();

            $reportCard = ReportCard::create([
                'student_id'       => $student->id,
                'classroom_id'     => $classroom->id,
                'academic_year_id' => $year->id,
                'semester'         => 1,
                'status'           => $status,
                'verify_code'      => $status === 'approved' ? Str::uuid()->toString() : null,
                'approved_at'      => $status === 'approved' ? now()->subDays(rand(1, 7)) : null,
                'approved_by'      => $status === 'approved' ? $kamad->id : null,
            ]);

            // Isi catatan untuk raport yang approved/waiting
            if (in_array($status, ['approved', 'waiting_approval'])) {
                ReportCardNote::create([
                    'report_card_id'  => $reportCard->id,
                    'homeroom_notes'  => 'Peserta didik menunjukkan perkembangan yang sangat baik selama semester ini. '
                                       . 'Diharapkan terus semangat belajar dan menjaga prestasi.',
                    'principal_notes' => $status === 'approved'
                        ? 'Selamat atas pencapaian selama semester ini. Pertahankan dan tingkatkan prestasimu.'
                        : null,
                ]);
            }
        }
    }
}
