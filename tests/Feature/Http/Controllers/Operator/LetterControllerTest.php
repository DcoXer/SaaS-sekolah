<?php

namespace Tests\Feature\Http\Controllers\Operator;

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

    private User $operator;
    private User $kamad;
    private LetterType $letterType;
    private LetterTemplate $template;
    private Student $student;
    private User $siswaUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->operator = User::factory()->create();
        $this->operator->assignRole('operator');

        $this->kamad = User::factory()->create();
        $this->kamad->assignRole('kamad');

        $this->siswaUser = User::factory()->create();
        $this->siswaUser->assignRole('siswa');

        $this->student = Student::create([
            'user_id' => $this->siswaUser->id,
            'nis'     => '001',
            'name'    => 'Ahmad',
            'gender'  => 'L',
            'grade'   => 4,
            'status'  => 'active',
        ]);

        $this->letterType = LetterType::create([
            'name'      => 'Surat Pemberitahuan',
            'category'  => 'pemberitahuan',
            'is_active' => true,
        ]);

        $this->template = LetterTemplate::create([
            'letter_type_id'         => $this->letterType->id,
            'name'                   => 'Template Pemberitahuan',
            'content'                => 'Kepada {{student.name}}, kami memberitahukan bahwa...',
            'available_placeholders' => [],
            'is_active'              => true,
        ]);
    }

    public function test_operator_can_view_letters(): void
    {
        $response = $this->actingAs($this->operator)
                         ->get(route('operator.letters.index'));

        $response->assertStatus(200);
    }

    public function test_guest_cannot_view_letters(): void
    {
        $response = $this->get(route('operator.letters.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_kamad_cannot_view_operator_letters(): void
    {
        $response = $this->actingAs($this->kamad)
                         ->get(route('operator.letters.index'));

        $response->assertStatus(403);
    }

    public function test_operator_can_send_notification_letter(): void
    {
        $response = $this->actingAs($this->operator)
                         ->post(route('operator.letters.store-notification'), [
                             'letter_template_id' => $this->template->id,
                             'content'            => 'Pemberitahuan kegiatan sekolah.',
                         ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('letters', [
            'letter_template_id' => $this->template->id,
            'category'           => 'pemberitahuan',
        ]);
    }

    public function test_store_notification_fails_without_content(): void
    {
        $response = $this->actingAs($this->operator)
                         ->post(route('operator.letters.store-notification'), [
                             'letter_template_id' => $this->template->id,
                         ]);

        $response->assertSessionHasErrors('content');
    }

    public function test_operator_can_submit_letter_for_approval(): void
    {
        $letterType = LetterType::create([
            'name'      => 'Surat Keterangan',
            'category'  => 'keterangan',
            'is_active' => true,
        ]);
        $keteranganTemplate = LetterTemplate::create([
            'letter_type_id'         => $letterType->id,
            'name'                   => 'Keterangan Aktif',
            'content'                => 'Konten surat.',
            'available_placeholders' => [],
            'is_active'              => true,
        ]);

        $letter = Letter::create([
            'letter_template_id' => $keteranganTemplate->id,
            'category'           => 'keterangan',
            'requested_by'       => $this->siswaUser->id,
            'student_id'         => $this->student->id,
            'status'             => 'draft',
            'content'            => 'Konten surat.',
        ]);

        $response = $this->actingAs($this->operator)
                         ->patch(route('operator.letters.submit', $letter));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('letters', [
            'id'     => $letter->id,
            'status' => 'waiting_approval',
        ]);
    }
}
