<?php

namespace Tests\Feature\Http\Controllers\Kamad;

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

    private User $kamad;
    private User $operator;
    private Letter $letter;
    private Student $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kamad = User::factory()->create();
        $this->kamad->assignRole('kamad');

        $this->operator = User::factory()->create();
        $this->operator->assignRole('operator');

        $siswaUser = User::factory()->create();
        $siswaUser->assignRole('siswa');

        $this->student = Student::create([
            'user_id' => $siswaUser->id,
            'nis'     => '001',
            'name'    => 'Ahmad',
            'gender'  => 'L',
            'grade'   => 4,
            'status'  => 'active',
        ]);

        $letterType = LetterType::create([
            'name'      => 'Surat Keterangan',
            'category'  => 'keterangan',
            'is_active' => true,
        ]);

        $template = LetterTemplate::create([
            'letter_type_id'         => $letterType->id,
            'name'                   => 'Keterangan Aktif',
            'content'                => 'Konten surat untuk {{student.name}}.',
            'available_placeholders' => [],
            'is_active'              => true,
        ]);

        $this->letter = new Letter([
            'letter_template_id' => $template->id,
            'category'           => 'keterangan',
            'requested_by'       => $siswaUser->id,
            'student_id'         => $this->student->id,
            'content'            => 'Konten surat.',
        ]);
        $this->letter->status = 'waiting_approval';
        $this->letter->save();
    }

    public function test_kamad_can_view_letters(): void
    {
        $response = $this->actingAs($this->kamad)
                         ->get(route('kamad.letters.index'));

        $response->assertStatus(200);
    }

    public function test_guest_cannot_view_letters(): void
    {
        $response = $this->get(route('kamad.letters.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_operator_cannot_view_kamad_letters(): void
    {
        $response = $this->actingAs($this->operator)
                         ->get(route('kamad.letters.index'));

        $response->assertStatus(403);
    }

    public function test_kamad_can_approve_letter(): void
    {
        $response = $this->actingAs($this->kamad)
                         ->patch(route('kamad.letters.approve', $this->letter));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('letters', [
            'id'     => $this->letter->id,
            'status' => 'approved',
        ]);
    }

    public function test_kamad_can_reject_letter(): void
    {
        $response = $this->actingAs($this->kamad)
                         ->patch(route('kamad.letters.reject', $this->letter), [
                             'rejection_note' => 'Surat tidak memenuhi persyaratan yang diperlukan.',
                         ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('letters', [
            'id'     => $this->letter->id,
            'status' => 'rejected',
        ]);
    }

    public function test_reject_fails_without_rejection_note(): void
    {
        $response = $this->actingAs($this->kamad)
                         ->patch(route('kamad.letters.reject', $this->letter), [
                             'rejection_note' => '',
                         ]);

        $response->assertSessionHasErrors('rejection_note');
    }

    public function test_reject_fails_with_too_short_note(): void
    {
        $response = $this->actingAs($this->kamad)
                         ->patch(route('kamad.letters.reject', $this->letter), [
                             'rejection_note' => 'Terlalu',
                         ]);

        $response->assertSessionHasErrors('rejection_note');
    }

    public function test_operator_cannot_approve_letter(): void
    {
        $response = $this->actingAs($this->operator)
                         ->patch(route('kamad.letters.approve', $this->letter));

        $response->assertStatus(403);
    }
}
