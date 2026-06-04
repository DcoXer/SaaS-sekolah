<?php

namespace Tests\Unit\Services;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\ReportCard;
use App\Models\Student;
use App\Models\User;
use App\Services\PredicateConfigService;
use App\Services\ReportCardService;
use App\Services\StudentAssessmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportCardServiceTest extends TestCase
{
    use RefreshDatabase;

    private ReportCardService $service;
    private AcademicYear $academicYear;
    private Classroom $classroom;
    private Student $student;
    private User $kamad;

    protected function setUp(): void
    {
        parent::setUp();

        $predicateService = new PredicateConfigService();
        $this->service = new ReportCardService(
            new StudentAssessmentService($predicateService),
            $predicateService,
        );

        $this->academicYear = AcademicYear::create([
            'name'       => '2024/2025',
            'start_date' => '2024-07-15',
            'end_date'   => '2025-06-30',
            'status'     => 'active',
        ]);

        $this->classroom = Classroom::create([
            'academic_year_id' => $this->academicYear->id,
            'name'             => 'Kelas 1A',
            'grade'            => 1,
        ]);

        $this->student = Student::create([
            'nisn'   => '1234567890',
            'nis'    => '001',
            'name'   => 'Ahmad',
            'gender' => 'L',
            'status' => 'active',
        ]);

        $this->classroom->students()->attach($this->student->id, [
            'academic_year_id' => $this->academicYear->id,
        ]);

        $this->kamad = User::factory()->create();
        $this->kamad->assignRole('kamad');
    }

    public function test_can_generate_report_cards_for_class(): void
    {
        $this->service->generateForClass($this->classroom, $this->academicYear, 1);

        $this->assertDatabaseHas('report_cards', [
            'student_id'       => $this->student->id,
            'classroom_id'     => $this->classroom->id,
            'academic_year_id' => $this->academicYear->id,
            'semester'         => 1,
            'status'           => 'draft',
        ]);
    }

    public function test_generate_is_idempotent(): void
    {
        $this->service->generateForClass($this->classroom, $this->academicYear, 1);
        $this->service->generateForClass($this->classroom, $this->academicYear, 1);

        $this->assertCount(1, ReportCard::all());
    }

    public function test_can_publish_report_card(): void
    {
        $reportCard = ReportCard::create([
            'student_id'       => $this->student->id,
            'classroom_id'     => $this->classroom->id,
            'academic_year_id' => $this->academicYear->id,
            'semester'         => 1,
            'status'           => 'draft',
        ]);

        $this->service->approve($reportCard, $this->kamad);

        $this->assertNotNull($reportCard->fresh()->verify_code);
        $this->assertNotNull($reportCard->fresh()->approved_at);
        $this->assertEquals($this->kamad->id, $reportCard->fresh()->approved_by);
    }

    public function test_can_publish_all_in_class(): void
    {
        $student2 = Student::create([
            'nisn'   => '9876543210',
            'nis'    => '002',
            'name'   => 'Budi',
            'gender' => 'L',
            'status' => 'active',
        ]);

        $this->classroom->students()->attach($student2->id, [
            'academic_year_id' => $this->academicYear->id,
        ]);

        $this->service->generateForClass($this->classroom, $this->academicYear, 1);

        // Set status to waiting_approval so approveAll can process them
        ReportCard::where('classroom_id', $this->classroom->id)->update(['status' => 'waiting_approval']);

        $this->service->approveAll(
            $this->classroom,
            $this->academicYear,
            1,
            $this->kamad
        );

        $this->assertCount(2, ReportCard::where('status', 'approved')->get());
    }

    public function test_can_update_notes(): void
    {
        $reportCard = ReportCard::create([
            'student_id'       => $this->student->id,
            'classroom_id'     => $this->classroom->id,
            'academic_year_id' => $this->academicYear->id,
            'semester'         => 1,
            'status'           => 'draft',
        ]);

        $this->service->updateNotes($reportCard, [
            'homeroom_notes'  => 'Siswa rajin dan aktif.',
            'principal_notes' => 'Pertahankan prestasi.',
        ]);

        $this->assertDatabaseHas('report_card_notes', [
            'report_card_id' => $reportCard->id,
            'homeroom_notes' => 'Siswa rajin dan aktif.',
        ]);
    }

    public function test_get_by_student_returns_correct_report_card(): void
    {
        $reportCard = ReportCard::create([
            'student_id'       => $this->student->id,
            'classroom_id'     => $this->classroom->id,
            'academic_year_id' => $this->academicYear->id,
            'semester'         => 1,
            'status'           => 'published',
        ]);

        $result = $this->service->getByStudent($this->student, $this->academicYear, 1);

        $this->assertNotNull($result);
        $this->assertEquals($reportCard->id, $result->id);
    }
}