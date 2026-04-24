<?php

namespace Tests\Feature\Http\Controllers\Kamad;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\ReportCard;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportCardControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $kamad;
    private User $operator;
    private AcademicYear $academicYear;
    private Classroom $classroom;
    private Student $student;
    private ReportCard $reportCard;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kamad = User::factory()->create();
        $this->kamad->assignRole('kamad');

        $this->operator = User::factory()->create();
        $this->operator->assignRole('operator');

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
            'nis'    => '001',
            'name'   => 'Ahmad',
            'gender' => 'L',
            'grade'  => 1,
            'status' => 'active',
        ]);

        $this->classroom->students()->attach($this->student->id, ['academic_year_id' => $this->academicYear->id]);

        $this->reportCard = ReportCard::create([
            'student_id'       => $this->student->id,
            'classroom_id'     => $this->classroom->id,
            'academic_year_id' => $this->academicYear->id,
            'semester'         => 1,
            'status'           => 'waiting_approval',
        ]);
    }

    public function test_kamad_can_view_report_card_index(): void
    {
        $response = $this->actingAs($this->kamad)
                         ->get(route('kamad.report-cards.index'));

        $response->assertStatus(200);
    }

    public function test_guest_cannot_view_report_cards(): void
    {
        $response = $this->get(route('kamad.report-cards.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_operator_cannot_view_kamad_report_cards(): void
    {
        $response = $this->actingAs($this->operator)
                         ->get(route('kamad.report-cards.index'));

        $response->assertStatus(403);
    }

    public function test_kamad_can_generate_report_cards(): void
    {
        $response = $this->actingAs($this->kamad)
                         ->post(route('kamad.report-cards.generate', $this->classroom), [
                             'semester' => 1,
                         ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    public function test_kamad_can_approve_report_card(): void
    {
        $response = $this->actingAs($this->kamad)
                         ->patch(route('kamad.report-cards.approve', $this->reportCard));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('report_cards', [
            'id'     => $this->reportCard->id,
            'status' => 'approved',
        ]);
    }

    public function test_cannot_approve_already_approved_report_card(): void
    {
        $this->reportCard->update(['status' => 'approved']);

        $response = $this->actingAs($this->kamad)
                         ->patch(route('kamad.report-cards.approve', $this->reportCard));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_cannot_approve_draft_report_card(): void
    {
        $this->reportCard->update(['status' => 'draft']);

        $response = $this->actingAs($this->kamad)
                         ->patch(route('kamad.report-cards.approve', $this->reportCard));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_kamad_can_approve_all_report_cards(): void
    {
        $response = $this->actingAs($this->kamad)
                         ->patch(route('kamad.report-cards.approve-all', $this->classroom), [
                             'semester' => 1,
                         ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }
}
