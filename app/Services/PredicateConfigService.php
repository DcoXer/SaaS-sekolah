<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\PredicateConfig;
use Illuminate\Database\Eloquent\Collection;

class PredicateConfigService
{
    public function getByAcademicYear(AcademicYear $academicYear): Collection
    {
        return PredicateConfig::where('academic_year_id', $academicYear->id)
                              ->orderBy('min_score', 'desc')
                              ->get();
    }

    public function sync(AcademicYear $academicYear, array $configs): void
    {
        // Hapus semua config lama
        PredicateConfig::where('academic_year_id', $academicYear->id)->delete();

        // Insert yang baru
        foreach ($configs as $config) {
            PredicateConfig::create([
                'academic_year_id' => $academicYear->id,
                'min_score'        => $config['min_score'],
                'max_score'        => $config['max_score'],
                'predicate'        => $config['predicate'],
            ]);
        }
    }

    public function getPredicateByScore(AcademicYear $academicYear, int $score): ?string
    {
        $config = PredicateConfig::where('academic_year_id', $academicYear->id)
                                 ->where('min_score', '<=', $score)
                                 ->where('max_score', '>=', $score)
                                 ->first();

        return $config?->predicate;
    }
}