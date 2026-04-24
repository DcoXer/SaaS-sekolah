<?php

namespace Tests\Feature\Http\Controllers\Guru;

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

    private User $guru;
    private Teacher $teacher;
    private AcademicYear $academicYear;
    private Classroom $classroom;
    private Student $student;
    private ReportCard $reportCard;

    protected function setUp(): void
    {
        parent::setUp();

        $guruUser = User::factory()->create();
        $guruUser->assignRole('guru');
        $this->guru = $guruUser;

        $this->teacher = Teacher::create([
            'user_id' => $guruUser->id,
            'gender'  => 'L',
            'type'    => 'guru_kelas',
        ]);

        $this->academicYear = AcademicYear::create([
            'name'       => '2024/2025',
            'start_date' => '2024-07-15',
            'end_date'   => '2025-06-30',
            'status'     => 'active',
        ]);

        $this->classroom = Classroom::create([
            'academic_year_id'   => $this->academicYear->id,
            'name'               => 'Kelas 1A',
            'grade'              => 1,
            'homeroom_teacher_id'=> $this->teacher->id,
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
            'status'           => 'draft',
        ]);
    }

    public function test_guru_can_view_report_card_index(): void
    {
        $response = $this->actingAs($this->guru)
                         ->get(route('guru.report-cards.index'));

        $response->assertStatus(200);
    }

    public function test_guest_cannot_view_report_cards(): void
    {
        $response = $this->get(route('guru.report-cards.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_guru_can_update_report_card_notes(): void
    {
        $response = $this->actingAs($this->guru)
                         ->patch(route('guru.report-cards.update-notes', $this->reportCard), [
                             'homeroom_notes' => 'Siswa rajin dan aktif.',
                         ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    public function test_non_homeroom_teacher_cannot_update_notes(): void
    {
        $otherUser = User::factory()->create();
        $otherUser->assignRole('guru');
        Teacher::create([
            'user_id' => $otherUser->id,
            'gender'  => 'P',
            'type'    => 'guru_kelas',
        ]);

        $response = $this->actingAs($otherUser)
                         ->patch(route('guru.report-cards.update-notes', $this->reportCard), [
                             'homeroom_notes' => 'Catatan.',
                         ]);

        $response->assertStatus(403);
    }

    public function test_guru_can_submit_draft_report_card(): void
    {
        $response = $this->actingAs($this->guru)
                         ->patch(route('guru.report-cards.submit', $this->reportCard));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('report_cards', [
            'id'     => $this->reportCard->id,
            'status' => 'waiting_approval',
        ]);
    }

    public function test_cannot_submit_already_submitted_report_card(): void
    {
        $this->reportCard->update(['status' => 'waiting_approval']);

        $response = $this->actingAs($this->guru)
                         ->patch(route('guru.report-cards.submit', $this->reportCard));

        $response->assertStatus(422);
    }

    public function test_non_homeroom_teacher_cannot_submit(): void
    {
        $otherUser = User::factory()->create();
        $otherUser->assignRole('guru');
        Teacher::create([
            'user_id' => $otherUser->id,
            'gender'  => 'P',
            'type'    => 'guru_kelas',
        ]);

        $response = $this->actingAs($otherUser)
                         ->patch(route('guru.report-cards.submit', $this->reportCard));

        $response->assertStatus(403);
    }
}
