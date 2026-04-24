<?php

namespace Tests\Unit\Services;

use App\Models\AcademicYear;
use App\Models\PredicateConfig;
use App\Services\PredicateConfigService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PredicateConfigServiceTest extends TestCase
{
    use RefreshDatabase;

    private PredicateConfigService $service;
    private AcademicYear $academicYear;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PredicateConfigService();

        $this->academicYear = AcademicYear::create([
            'name'       => '2024/2025',
            'start_date' => '2024-07-15',
            'end_date'   => '2025-06-30',
            'status'     => 'active',
        ]);
    }

    public function test_can_sync_predicate_configs(): void
    {
        $this->service->sync($this->academicYear, [
            ['min_score' => 90, 'max_score' => 100, 'predicate' => 'A'],
            ['min_score' => 80, 'max_score' => 89,  'predicate' => 'B'],
            ['min_score' => 70, 'max_score' => 79,  'predicate' => 'C'],
            ['min_score' => 0,  'max_score' => 69,  'predicate' => 'D'],
        ]);

        $this->assertCount(4, PredicateConfig::where('academic_year_id', $this->academicYear->id)->get());
    }

    public function test_sync_replaces_existing_configs(): void
    {
        $this->service->sync($this->academicYear, [
            ['min_score' => 90, 'max_score' => 100, 'predicate' => 'A'],
            ['min_score' => 0,  'max_score' => 89,  'predicate' => 'B'],
        ]);

        // Sync ulang dengan data berbeda
        $this->service->sync($this->academicYear, [
            ['min_score' => 85, 'max_score' => 100, 'predicate' => 'A'],
            ['min_score' => 70, 'max_score' => 84,  'predicate' => 'B'],
            ['min_score' => 0,  'max_score' => 69,  'predicate' => 'C'],
        ]);

        $this->assertCount(3, PredicateConfig::where('academic_year_id', $this->academicYear->id)->get());
    }

    public function test_get_predicate_by_score_returns_correct_predicate(): void
    {
        $this->service->sync($this->academicYear, [
            ['min_score' => 90, 'max_score' => 100, 'predicate' => 'A'],
            ['min_score' => 80, 'max_score' => 89,  'predicate' => 'B'],
            ['min_score' => 70, 'max_score' => 79,  'predicate' => 'C'],
            ['min_score' => 0,  'max_score' => 69,  'predicate' => 'D'],
        ]);

        $this->assertEquals('A', $this->service->getPredicateByScore($this->academicYear, 95));
        $this->assertEquals('B', $this->service->getPredicateByScore($this->academicYear, 85));
        $this->assertEquals('C', $this->service->getPredicateByScore($this->academicYear, 75));
        $this->assertEquals('D', $this->service->getPredicateByScore($this->academicYear, 60));
    }

    public function test_get_predicate_returns_null_when_no_config(): void
    {
        $predicate = $this->service->getPredicateByScore($this->academicYear, 90);

        $this->assertNull($predicate);
    }

    public function test_get_by_academic_year_ordered_by_min_score_desc(): void
    {
        $this->service->sync($this->academicYear, [
            ['min_score' => 0,  'max_score' => 69,  'predicate' => 'D'],
            ['min_score' => 90, 'max_score' => 100, 'predicate' => 'A'],
            ['min_score' => 70, 'max_score' => 79,  'predicate' => 'C'],
            ['min_score' => 80, 'max_score' => 89,  'predicate' => 'B'],
        ]);

        $configs = $this->service->getByAcademicYear($this->academicYear);

        $this->assertEquals('A', $configs->first()->predicate);
        $this->assertEquals('D', $configs->last()->predicate);
    }
}