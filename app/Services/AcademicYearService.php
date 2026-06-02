<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Student;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class AcademicYearService
{
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
        });
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
                                      fn($q2) => $q2->where('academic_year_id', $previousYearId)
                                  ))
                                  ->with('user')
                                  ->get();

            foreach ($graduating as $student) {
                $student->update(['status' => 'alumni']);

                if ($student->user) {
                    $student->user->update([
                        'alumni_expires_at' => now()->addYears(5),
                    ]);
                }
            }

            // Grade 1-5 → naik satu tingkat
            // Hanya siswa yang punya enrollment di tahun ajaran sebelumnya
            Student::where('status', 'active')
                   ->whereBetween('grade', [1, 5])
                   ->when($previousYearId, fn($q) => $q->whereHas(
                       'classrooms',
                       fn($q2) => $q2->where('academic_year_id', $previousYearId)
                   ))
                   ->increment('grade');
        });
    }

    public function close(AcademicYear $academicYear): void
    {
        $academicYear->update(['status' => 'closed']);
    }
}