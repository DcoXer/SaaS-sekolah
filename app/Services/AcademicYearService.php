<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Invoice;
use App\Models\PpdbRegistration;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class AcademicYearService
{
    public function __construct(private StudentService $studentService) {}
    public function getAll(): Collection
    {
        return AcademicYear::latest()->get();
    }

    public function getActive(): ?AcademicYear
    {
        return AcademicYear::where('status', 'active')->first();
    }

    public function create(array $data): AcademicYear
    {
        return AcademicYear::create([
            'name'       => $data['name'],
            'start_date' => $data['start_date'],
            'end_date'   => $data['end_date'],
            'status'     => 'pending',
        ]);
    }

    public function approve(AcademicYear $academicYear): void
    {
        // Bungkus dalam transaction agar close → activate → promote berjalan atomic
        DB::transaction(function () use ($academicYear) {
            // Catat tahun yang sedang active sebelum ditutup
            $previousActiveYear = AcademicYear::where('status', 'active')->first();

            // Tutup semua yang active dulu
            AcademicYear::where('status', 'active')->update(['status' => 'closed']);

            // Activate yang baru
            $academicYear->update(['status' => 'active']);

            // Auto promosi siswa yang terdaftar di tahun ajaran yang baru saja ditutup
            $this->promoteStudents($previousActiveYear?->id);

            // Convert pendaftar PPDB yang diterima → jadi siswa baru grade 1
            $this->convertAcceptedPpdbToStudents();
        });
    }

    private function convertAcceptedPpdbToStudents(): void
    {
        // Ambil semua pendaftar PPDB yang diterima dan belum diconvert jadi siswa
        $registrations = PpdbRegistration::where('status', 'accepted')
            ->whereNull('student_id')
            ->get();

        foreach ($registrations as $reg) {
            // Kalau email sudah dipakai akun lain, buat siswa tanpa akun user
            $email = $reg->parent_email;
            if ($email && User::where('email', $email)->exists()) {
                $email = null;
            }

            $student = $this->studentService->create([
                'name'          => $reg->full_name,
                'nik'           => $reg->nik_siswa,
                'gender'        => $reg->gender,
                'birth_place'   => $reg->birth_place,
                'birth_date'    => $reg->birth_date?->toDateString(),
                'address'       => $reg->address,
                'father_name'   => $reg->father_name,
                'mother_name'   => $reg->mother_name,
                'guardian_name' => $reg->parent_name,
                'grade'         => 1, // PPDB selalu masuk kelas 1
                'email'         => $email,
                'parent_name'   => $reg->parent_name,
                'password'      => null,
            ]);

            $reg->update(['student_id' => $student->id]);

            // Transfer invoice uang masuk dari ppdb_registration ke student
            // ppdb_registration_id tetap dipertahankan sebagai marker invoice PPDB
            Invoice::where('ppdb_registration_id', $reg->id)
                ->whereNull('student_id')
                ->update(['student_id' => $student->id]);
        }
    }

    private function promoteStudents(?int $previousYearId): void
    {
        DB::transaction(function () use ($previousYearId) {
            // Grade 6 → alumni, set expiry akun 5 tahun
            // Hanya siswa yang punya enrollment di tahun ajaran sebelumnya
            $graduating = Student::where('status', 'active')
                                  ->where('grade', 6)
                                  ->when($previousYearId, fn($q) => $q->whereHas(
                                      'classrooms',
                                      fn($q2) => $q2->where('classrooms.academic_year_id', $previousYearId)
                                  ))
                                  ->with('user')
                                  ->get();

            foreach ($graduating as $student) {
                $student->update(['status' => 'alumni']);

                if ($student->user) {
                    $student->user->alumni_expires_at = now()->addYears(5);
                    $student->user->save();
                }
            }

            // Grade 1-5 → naik satu tingkat
            // Hanya siswa yang punya enrollment di tahun ajaran sebelumnya
            Student::where('status', 'active')
                   ->whereBetween('grade', [1, 5])
                   ->when($previousYearId, fn($q) => $q->whereHas(
                       'classrooms',
                       fn($q2) => $q2->where('classrooms.academic_year_id', $previousYearId)
                   ))
                   ->increment('grade');
        });
    }

    public function close(AcademicYear $academicYear): void
    {
        $academicYear->update(['status' => 'closed']);
    }
}