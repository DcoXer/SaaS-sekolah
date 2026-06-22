<?php

namespace Tests\Feature\Http\Controllers\Siswa;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Letter;
use App\Models\LetterTemplate;
use App\Models\LetterType;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LetterControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $siswa;
    private User $otherSiswa;
    private User $operator;
    private Student $student;
    private LetterTemplate $template;
    private Letter $letter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->siswa = User::factory()->create();
        $this->siswa->assignRole('siswa');

        $this->otherSiswa = User::factory()->create();
        $this->otherSiswa->assignRole('siswa');

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

        // Setup academic year + classroom agar requestLetter() tidak abort dengan 422
        $activeYear = AcademicYear::create([
            'name'       => '2025/2026',
            'start_date' => '2025-07-01',
            'end_date'   => '2026-06-30',
            'status'     => 'active',
        ]);

        $classroom = Classroom::create([
            'academic_year_id' => $activeYear->id,
            'name'             => 'Kelas 4A',
            'grade'            => 4,
        ]);

        $this->student->classrooms()->attach($classroom->id, [
            'academic_year_id' => $activeYear->id,
        ]);

        $letterType = LetterType::create([
            'name'      => 'Surat Keterangan Aktif',
            'category'  => 'keterangan',
            'is_active' => true,
        ]);

        $this->template = LetterTemplate::create([
            'letter_type_id'         => $letterType->id,
            'name'                   => 'Keterangan Aktif',
            'content'                => 'Konten surat untuk {{student.name}}.',
            'available_placeholders' => [],
            'is_active'              => true,
        ]);

        $this->letter = Letter::create([
            'letter_template_id' => $this->template->id,
            'category'           => 'keterangan',
            'requested_by'       => $this->siswa->id,
            'student_id'         => $this->student->id,
            'status'             => 'draft',
            'content'            => 'Konten surat.',
        ]);
    }

    public function test_siswa_can_view_letters(): void
    {
        $response = $this->actingAs($this->siswa)
                         ->get(route('siswa.letters.index'));

        $response->assertStatus(200);
    }

    public function test_guest_cannot_view_letters(): void
    {
        $response = $this->get(route('siswa.letters.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_operator_cannot_view_siswa_letters(): void
    {
        $response = $this->actingAs($this->operator)
                         ->get(route('siswa.letters.index'));

        $response->assertStatus(403);
    }

    public function test_siswa_can_request_letter(): void
    {
        $response = $this->actingAs($this->siswa)
                         ->post(route('siswa.letters.store'), [
                             'letter_template_id' => $this->template->id,
                         ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('letters', [
            'student_id'         => $this->student->id,
            'letter_template_id' => $this->template->id,
            'category'           => 'keterangan',
        ]);
    }

    public function test_store_fails_with_invalid_template(): void
    {
        $response = $this->actingAs($this->siswa)
                         ->post(route('siswa.letters.store'), [
                             'letter_template_id' => 99999,
                         ]);

        $response->assertSessionHasErrors('letter_template_id');
    }

    public function test_siswa_can_view_own_letter(): void
    {
        $response = $this->actingAs($this->siswa)
                         ->get(route('siswa.letters.show', $this->letter));

        $response->assertStatus(200);
    }

    public function test_siswa_cannot_view_other_student_letter(): void
    {
        // otherSiswa has no student record, so student_id mismatch → 403
        $response = $this->actingAs($this->otherSiswa)
                         ->get(route('siswa.letters.show', $this->letter));

        $response->assertStatus(403);
    }

    public function test_siswa_can_mark_notification_as_read(): void
    {
        $notifType = LetterType::create([
            'name'      => 'Pemberitahuan',
            'category'  => 'pemberitahuan',
            'is_active' => true,
        ]);
        $notifTemplate = LetterTemplate::create([
            'letter_type_id'         => $notifType->id,
            'name'                   => 'Template Notif',
            'content'                => 'Isi pemberitahuan.',
            'available_placeholders' => [],
            'is_active'              => true,
        ]);

        $notifLetter = Letter::create([
            'letter_template_id' => $notifTemplate->id,
            'category'           => 'pemberitahuan',
            'requested_by'       => $this->operator->id,
            'status'             => 'published',
            'content'            => 'Isi pemberitahuan.',
        ]);

        $notifLetter->recipients()->create(['student_id' => $this->student->id]);

        $response = $this->actingAs($this->siswa)
                         ->patch(route('siswa.letters.read', $notifLetter));

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }
}
