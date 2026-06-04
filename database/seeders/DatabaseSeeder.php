<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Buat roles Spatie
        $roles = ['kamad', 'operator', 'tu_keuangan', 'guru', 'siswa'];
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        $this->call([
            // ── Master data ──────────────────────────────────────────────────
            UserSeeder::class,
            SchoolSettingSeeder::class,
            AcademicYearSeeder::class,
            TeacherSeeder::class,
            SubjectSeeder::class,       // sebelum Classroom (assignGuruKelas butuh subject)
            ClassroomSeeder::class,
            StudentSeeder::class,

            // ── Akademik ─────────────────────────────────────────────────────
            PredicateConfigSeeder::class,
            AssessmentComponentSeeder::class,
            StudentAssessmentSeeder::class,
            ReportCardSeeder::class,

            // ── Keuangan ─────────────────────────────────────────────────────
            PaymentSeeder::class,

            // ── Surat ────────────────────────────────────────────────────────
            LetterSeeder::class,

            // ── Profil Publik ─────────────────────────────────────────────────
            ExtracurricularSeeder::class,
            SchoolGallerySeeder::class,
            SchoolPostSeeder::class,
            SchoolHeroPhotoSeeder::class,
        ]);

        // Pastikan semua user bisa login (email verified)
        User::whereNull('email_verified_at')->update([
            'email_verified_at' => now(),
        ]);
    }
}
