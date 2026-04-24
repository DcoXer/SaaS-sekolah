<?php

namespace Tests\Feature\Http\Controllers\Siswa;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\ReportCard;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportCardControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $siswa;
    private User $operator;
    private Student $student;
    private AcademicYear $academicYear;
    private Classroom $classroom;
    private ReportCard $reportCard;

    protected function setUp(): void
    {
        parent::setUp();

        $this->siswa = User::factory()->create();
        $this->siswa->assignRole('siswa');

        $this->operator = User::factory()->create();
        $this->operator->assignRole('operator');

        $this->student = Student::create([
            'user_id' => $this->siswa->id,
            'nis'     => '001',
            'name'    => 'Ahmad',
            'gender'  => 'L',
            'grade'   => 4,
            'status'  => 'active',
        ]);

        $this->academicYear = AcademicYear::create([
            'name'       => '2024/2025',
            'start_date' => '2024-07-15',
            'end_date'   => '2025-06-30',
            'status'     => 'active',
        ]);

        $this->classroom = Classroom::create([
            'academic_year_id' => $this->academicYear->id,
            'name'             => 'Kelas 4A',
            'grade'            => 4,
        ]);

        $this->classroom->students()->attach($this->student->id, ['academic_year_id' => $this->academicYear->id]);

        $this->reportCard = ReportCard::create([
            'student_id'       => $this->student->id,
            'classroom_id'     => $this->classroom->id,
            'academic_year_id' => $this->academicYear->id,
            'semester'         => 1,
            'status'           => 'approved',
        ]);
    }

    public function test_siswa_can_view_report_card_index(): void
    {
        $response = $this->actingAs($this->siswa)
                         ->get(route('siswa.report-cards.index'));

        $response->assertStatus(200);
    }

    public function test_guest_cannot_view_report_cards(): void
    {
        $response = $this->get(route('siswa.report-cards.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_operator_cannot_view_siswa_report_cards(): void
    {
        $response = $this->actingAs($this->operator)
                         ->get(route('siswa.report-cards.index'));

        $response->assertStatus(403);
    }

    public function test_siswa_can_view_approved_report_card(): void
    {
        $response = $this->actingAs($this->siswa)
                         ->get(route('siswa.report-cards.show', 1));

        $response->assertStatus(200);
    }

    public function test_siswa_cannot_view_draft_report_card(): void
    {
        $this->reportCard->update(['status' => 'draft']);

        $response = $this->actingAs($this->siswa)
                         ->get(route('siswa.report-cards.show', 1));

        $response->assertStatus(404);
    }

    public function test_returns_404_without_active_year(): void
    {
        $this->academicYear->update(['status' => 'closed']);

        $response = $this->actingAs($this->siswa)
                         ->get(route('siswa.report-cards.show', 1));

        $response->assertStatus(404);
    }
}
