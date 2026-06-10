<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [
            'Pendidikan Agama Islam',
            'Bahasa Indonesia',
            'Matematika',
            'PPKn',
            'PJOK',
            'SBdP',
            'IPA',
            'IPS',
            'Bahasa Inggris',
        ];

        foreach ($subjects as $name) {
            Subject::firstOrCreate(['name' => $name]);
        }
    }
}
