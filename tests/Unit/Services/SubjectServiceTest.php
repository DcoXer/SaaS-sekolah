<?php

namespace Tests\Unit\Services;

use App\Models\Subject;
use App\Services\SubjectService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubjectServiceTest extends TestCase
{
    use RefreshDatabase;

    private SubjectService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SubjectService();
    }

    public function test_can_create_subject(): void
    {
        $subject = $this->service->create([
            'name'  => 'Matematika',
            'grade' => 1,
        ]);

        $this->assertInstanceOf(Subject::class, $subject);
        $this->assertDatabaseHas('subjects', [
            'name'  => 'Matematika',
            'grade' => 1,
        ]);
    }

    public function test_can_update_subject(): void
    {
        $subject = Subject::create(['name' => 'Matematika', 'grade' => 1]);

        $updated = $this->service->update($subject, [
            'name'  => 'Matematika Updated',
            'grade' => 2,
        ]);

        $this->assertEquals('Matematika Updated', $updated->name);
        $this->assertEquals(2, $updated->grade);
    }

    public function test_can_delete_subject(): void
    {
        $subject = Subject::create(['name' => 'Matematika', 'grade' => 1]);

        $this->service->delete($subject);

        $this->assertDatabaseMissing('subjects', ['id' => $subject->id]);
    }

    public function test_get_by_grade_returns_correct_subjects(): void
    {
        Subject::create(['name' => 'Matematika', 'grade' => 1]);
        Subject::create(['name' => 'IPA', 'grade' => 1]);
        Subject::create(['name' => 'Bahasa Indonesia', 'grade' => 2]);

        $subjects = $this->service->getByGrade(1);

        $this->assertCount(2, $subjects);
        $subjects->each(fn($s) => $this->assertEquals(1, $s->grade));
    }
}