<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Letter;
use App\Models\LetterRecipient;
use App\Models\LetterTemplate;
use App\Models\SchoolSetting;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LetterService
{
    public function __construct(private AcademicYearService $academicYearService) {}

    public function getAll(): Collection
    {
        return Letter::with(['letterTemplate.letterType', 'student', 'requestedBy'])
                     ->latest()
                     ->get();
    }

    public function getByStatus(string $status): Collection
    {
        return Letter::with(['letterTemplate.letterType', 'student', 'requestedBy'])
                     ->where('status', $status)
                     ->latest()
                     ->get();
    }

    public function getByStudent(Student $student): Collection
    {
        return Letter::with(['letterTemplate.letterType'])
                     ->where('student_id', $student->id)
                     ->where('category', 'keterangan')
                     ->latest()
                     ->get();
    }

    public function getNotificationsForStudent(Student $student): Collection
    {
        return LetterRecipient::with(['letter.letterTemplate.letterType'])
                              ->where('student_id', $student->id)
                              ->latest()
                              ->get();
    }

    public function requestLetter(
        LetterTemplate $template,
        User $requestedBy,
        Student $student
    ): Letter {
        $activeYear = $this->academicYearService->getActive();
        $classroom  = $student->classrooms()
                              ->wherePivot('academic_year_id', $activeYear?->id)
                              ->first();

        // Replace placeholders dengan data siswa
        $content = $this->replacePlaceholders($template->content, $student, $classroom, $activeYear);

        return Letter::create([
            'letter_template_id' => $template->id,
            'category'           => 'keterangan',
            'requested_by'       => $requestedBy->id,
            'student_id'         => $student->id,
            'status'             => 'draft',
            'content'            => $content,
        ]);
    }

    public function submitForApproval(Letter $letter): void
    {
        abort_if(!$letter->isDraft(), 400, 'Surat tidak dalam status draft.');

        $letter->update(['status' => 'waiting_approval']);
    }

    public function approve(Letter $letter, User $kamad): void
    {
        abort_if(!$letter->isWaitingApproval(), 400, 'Surat tidak dalam status menunggu persetujuan.');

        $barcodeCode = Str::uuid()->toString();

        $letter->update([
            'status'       => 'approved',
            'approved_by'  => $kamad->id,
            'approved_at'  => now(),
            'barcode_code' => $barcodeCode,
        ]);
    }

    public function reject(Letter $letter, User $kamad, string $rejectionNote): void
    {
        abort_if(!$letter->isWaitingApproval(), 400, 'Surat tidak dalam status menunggu persetujuan.');

        $letter->update([
            'status'         => 'rejected',
            'approved_by'    => $kamad->id,
            'rejection_note' => $rejectionNote,
        ]);
    }

    public function createNotification(
        LetterTemplate $template,
        User $operator,
        array $data
    ): Letter {
        $letter = Letter::create([
            'letter_template_id' => $template->id,
            'category'           => 'pemberitahuan',
            'requested_by'       => $operator->id,
            'target_grade'       => $data['target_grade'] ?? null,
            'status'             => 'published',
            'content'            => $data['content'],
            'published_at'       => now(),
        ]);

        // Generate recipients
        $this->generateRecipients($letter);

        return $letter;
    }

    public function generateRecipients(Letter $letter): void
    {
        DB::transaction(function () use ($letter) {
            $activeYear = $this->academicYearService->getActive();

            $query = Student::where('status', 'active');

            if ($letter->target_grade) {
                $query->whereHas('classrooms', function ($q) use ($letter, $activeYear) {
                    $q->where('classrooms.grade', $letter->target_grade)
                      ->where('student_classrooms.academic_year_id', $activeYear?->id);
                });
            }

            $students = $query->get();

            foreach ($students as $student) {
                LetterRecipient::firstOrCreate([
                    'letter_id'  => $letter->id,
                    'student_id' => $student->id,
                ]);
            }
        });
    }

    public function markAsRead(Letter $letter, Student $student): void
    {
        $recipient = LetterRecipient::where('letter_id', $letter->id)
                                    ->where('student_id', $student->id)
                                    ->first();

        $recipient?->markAsRead();
    }

    public function verifyBarcode(string $barcodeCode): ?Letter
    {
        return Letter::with(['letterTemplate.letterType', 'student', 'approvedBy'])
                     ->where('barcode_code', $barcodeCode)
                     ->where('status', 'approved')
                     ->first();
    }

    private function replacePlaceholders(
        string $content,
        Student $student,
        $classroom,
        $academicYear
    ): string {
        $school = SchoolSetting::current();

        $e = fn (?string $v) => htmlspecialchars($v ?? '-', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        $replacements = [
            '{{student.name}}'       => $e($student->name),
            '{{student.nis}}'        => $e($student->nis),
            '{{classroom.name}}'     => $e($classroom?->name),
            '{{academic_year.name}}' => $e($academicYear?->name),
            '{{letter.date}}'        => $e(now()->isoFormat('D MMMM YYYY')),
            '{{letter.number}}'      => 'No. ' . $e(now()->format('Ymd') . '/' . $student->nis),
            '{{principal.name}}'     => $e($school?->principal_name),
            '{{principal.nip}}'      => $e($school?->principal_nip),
            '{{school.name}}'        => $e($school?->name),
            '{{school.address}}'     => $e($school?->address),
            '{{school.phone}}'       => $e($school?->phone),
            '{{barcode}}'            => '', // akan di-replace waktu generate PDF
        ];

        return str_replace(
            array_keys($replacements),
            array_values($replacements),
            $content
        );
    }
}