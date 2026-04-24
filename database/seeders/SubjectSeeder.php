<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        // Mapel kelas 1–3 (diajar guru kelas, semua mapel per kelas)
        $mapelBawah = [
            'Pendidikan Agama Islam',
            'Bahasa Indonesia',
            'Matematika',
            'PPKn',
            'PJOK',
            'SBdP',
        ];

        // Mapel kelas 4–6 (diajar guru mapel spesifik)
        $mapelAtas = [
            'Pendidikan Agama Islam',
            'Bahasa Indonesia',
            'Matematika',
            'IPA',
            'IPS',
            'PPKn',
            'PJOK',
            'SBdP',
            'Bahasa Inggris',
        ];

        foreach (range(1, 3) as $grade) {
            foreach ($mapelBawah as $name) {
                Subject::create(['name' => $name, 'grade' => $grade]);
            }
        }

        foreach (range(4, 6) as $grade) {
            foreach ($mapelAtas as $name) {
                Subject::create(['name' => $name, 'grade' => $grade]);
            }
        }
    }
}
