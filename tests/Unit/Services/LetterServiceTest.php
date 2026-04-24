<?php

namespace Tests\Unit\Services;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Letter;
use App\Models\LetterRecipient;
use App\Models\LetterTemplate;
use App\Models\LetterType;
use App\Models\SchoolSetting;
use App\Models\Student;
use App\Models\User;
use App\Services\AcademicYearService;
use App\Services\LetterService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LetterServiceTest extends TestCase
{
    use RefreshDatabase;

    private LetterService $service;
    private AcademicYear $academicYear;
    private Classroom $classroom;
    private Student $student;
    private User $waliUser;
    private User $operatorUser;
    private User $kamadUser;
    private LetterTemplate $template;
    private LetterTemplate $notifTemplate;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new LetterService(new AcademicYearService());

        SchoolSetting::create([
            'name'           => 'MI Darul Hasan',
            'principal_name' => 'Ahmad Fauzi',
            'address'        => 'Jl. Raya No. 1',
        ]);

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

        $this->waliUser = User::factory()->create();
        $this->waliUser->assignRole('siswa');

        $this->student = Student::create([
            'user_id' => $this->waliUser->id,
            'nis'     => '001',
            'name'    => 'Ahmad',
            'gender'  => 'L',
            'status'  => 'active',
        ]);

        $this->classroom->students()->attach($this->student->id, [
            'academic_year_id' => $this->academicYear->id,
        ]);

        $this->operatorUser = User::factory()->create();
        $this->operatorUser->assignRole('operator');

        $this->kamadUser = User::factory()->create();
        $this->kamadUser->assignRole('kamad');

        $letterType = LetterType::create([
            'name'      => 'Surat Keterangan',
            'category'  => 'keterangan',
            'is_active' => true,
        ]);

        $notifType = LetterType::create([
            'name'      => 'Surat Pemberitahuan',
            'category'  => 'pemberitahuan',
            'is_active' => true,
        ]);

        $this->template = LetterTemplate::create([
            'letter_type_id'         => $letterType->id,
            'name'                   => 'Surat Keterangan Siswa Aktif',
            'content'                => 'Menerangkan bahwa {{student.name}} NIS {{student.nis}} adalah siswa aktif kelas {{classroom.name}}',
            'available_placeholders' => ['{{student.name}}', '{{student.nis}}', '{{classroom.name}}'],
            'is_active'              => true,
        ]);

        $this->notifTemplate = LetterTemplate::create([
            'letter_type_id'         => $notifType->id,
            'name'                   => 'Pemberitahuan Libur',
            'content'                => 'Pemberitahuan libur sekolah',
            'available_placeholders' => ['{{letter.date}}'],
            'is_active'              => true,
        ]);
    }

    public function test_can_request_letter(): void
    {
        $letter = $this->service->requestLetter(
            $this->template,
            $this->waliUser,
            $this->student
        );

        $this->assertInstanceOf(Letter::class, $letter);
        $this->assertEquals('draft', $letter->status);
        $this->assertEquals($this->student->id, $letter->student_id);
    }

    public function test_request_letter_replaces_placeholders(): void
    {
        $letter = $this->service->requestLetter(
            $this->template,
            $this->waliUser,
            $this->student
        );

        $this->assertStringContainsString('Ahmad', $letter->content);
        $this->assertStringContainsString('001', $letter->content);
        $this->assertStringContainsString('Kelas 1A', $letter->content);
        $this->assertStringNotContainsString('{{student.name}}', $letter->content);
    }

    public function test_can_submit_for_approval(): void
    {
        $letter = $this->service->requestLetter(
            $this->template,
            $this->waliUser,
            $this->student
        );

        $this->service->submitForApproval($letter);

        $this->assertEquals('waiting_approval', $letter->fresh()->status);
    }

    public function test_can_approve_letter(): void
    {
        $letter = Letter::create([
            'letter_template_id' => $this->template->id,
            'category'           => 'keterangan',
            'requested_by'       => $this->waliUser->id,
            'student_id'         => $this->student->id,
            'status'             => 'waiting_approval',
            'content'            => 'Konten surat',
        ]);

        $this->service->approve($letter, $this->kamadUser);

        $approved = $letter->fresh();

        $this->assertEquals('approved', $approved->status);
        $this->assertNotNull($approved->barcode_code);
        $this->assertNotNull($approved->approved_at);
        $this->assertEquals($this->kamadUser->id, $approved->approved_by);
    }

    public function test_can_reject_letter(): void
    {
        $letter = Letter::create([
            'letter_template_id' => $this->template->id,
            'category'           => 'keterangan',
            'requested_by'       => $this->waliUser->id,
            'student_id'         => $this->student->id,
            'status'             => 'waiting_approval',
            'content'            => 'Konten surat',
        ]);

        $this->service->reject($letter, $this->kamadUser, 'Data tidak lengkap.');

        $rejected = $letter->fresh();

        $this->assertEquals('rejected', $rejected->status);
        $this->assertEquals('Data tidak lengkap.', $rejected->rejection_note);
    }

    public function test_can_create_notification_letter(): void
    {
        $letter = $this->service->createNotification(
            $this->notifTemplate,
            $this->operatorUser,
            ['content' => 'Pemberitahuan libur sekolah']
        );

        $this->assertEquals('published', $letter->status);
        $this->assertNotNull($letter->published_at);
    }

    public function test_notification_generates_recipients_for_all_students(): void
    {
        $this->service->createNotification(
            $this->notifTemplate,
            $this->operatorUser,
            ['content' => 'Pemberitahuan libur sekolah']
        );

        $this->assertDatabaseHas('letter_recipients', [
            'student_id' => $this->student->id,
        ]);
    }

    public function test_notification_generates_recipients_for_specific_grade(): void
    {
        $student2 = Student::create([
            'nis'    => '002',
            'name'   => 'Budi',
            'gender' => 'L',
            'status' => 'active',
        ]);

        $classroom2 = Classroom::create([
            'academic_year_id' => $this->academicYear->id,
            'name'             => 'Kelas 2A',
            'grade'            => 2,
        ]);

        $classroom2->students()->attach($student2->id, [
            'academic_year_id' => $this->academicYear->id,
        ]);

        $this->service->createNotification(
            $this->notifTemplate,
            $this->operatorUser,
            [
                'content'      => 'Pemberitahuan khusus kelas 1',
                'target_grade' => 1,
            ]
        );

        // Hanya student grade 1 yang dapat notif
        $this->assertDatabaseHas('letter_recipients', [
            'student_id' => $this->student->id,
        ]);

        $this->assertDatabaseMissing('letter_recipients', [
            'student_id' => $student2->id,
        ]);
    }

    public function test_can_verify_barcode(): void
    {
        $letter = Letter::create([
            'letter_template_id' => $this->template->id,
            'category'           => 'keterangan',
            'requested_by'       => $this->waliUser->id,
            'student_id'         => $this->student->id,
            'status'             => 'approved',
            'content'            => 'Konten surat',
            'barcode_code'       => 'test-barcode-123',
            'approved_by'        => $this->kamadUser->id,
            'approved_at'        => now(),
        ]);

        $verified = $this->service->verifyBarcode('test-barcode-123');

        $this->assertNotNull($verified);
        $this->assertEquals($letter->id, $verified->id);
    }

    public function test_verify_barcode_returns_null_for_invalid_code(): void
    {
        $verified = $this->service->verifyBarcode('invalid-barcode');

        $this->assertNull($verified);
    }

    public function test_can_mark_notification_as_read(): void
    {
        $letter = $this->service->createNotification(
            $this->notifTemplate,
            $this->operatorUser,
            ['content' => 'Pemberitahuan libur sekolah']
        );

        $this->service->markAsRead($letter, $this->student);

        $recipient = LetterRecipient::where('letter_id', $letter->id)
                                    ->where('student_id', $this->student->id)
                                    ->first();

        $this->assertNotNull($recipient->read_at);
    }

    public function test_approve_only_works_on_waiting_approval(): void
    {
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        $letter = Letter::create([
            'letter_template_id' => $this->template->id,
            'category'           => 'keterangan',
            'requested_by'       => $this->waliUser->id,
            'student_id'         => $this->student->id,
            'status'             => 'draft',
            'content'            => 'Konten surat',
        ]);

        $this->service->approve($letter, $this->kamadUser);
    }
}