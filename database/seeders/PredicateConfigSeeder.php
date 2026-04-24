<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\PredicateConfig;
use Illuminate\Database\Seeder;

class PredicateConfigSeeder extends Seeder
{
    public function run(): void
    {
        $year = AcademicYear::where('status', 'active')->firstOrFail();

        $predicates = [
            ['predicate' => 'A', 'min_score' => 86, 'max_score' => 100],
            ['predicate' => 'B', 'min_score' => 71, 'max_score' => 85],
            ['predicate' => 'C', 'min_score' => 56, 'max_score' => 70],
            ['predicate' => 'D', 'min_score' => 0,  'max_score' => 55],
        ];

        foreach ($predicates as $data) {
            PredicateConfig::create([
                'academic_year_id' => $year->id,
                'predicate'        => $data['predicate'],
                'min_score'        => $data['min_score'],
                'max_score'        => $data['max_score'],
            ]);
        }
    }
}
