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
        // Tutup semua yang active dulu
        AcademicYear::where('status', 'active')->update(['status' => 'closed']);

        // Activate yang baru
        $academicYear->update(['status' => 'active']);

        // Auto promosi siswa
        $this->promoteStudents();
    }

    private function promoteStudents(): void
    {
        DB::transaction(function () {
            // Grade 6 → alumni, set expiry akun 5 tahun
            $graduating = Student::where('status', 'active')
                                  ->where('grade', 6)
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
            Student::where('status', 'active')
                   ->whereBetween('grade', [1, 5])
                   ->increment('grade');
        });
    }

    public function close(AcademicYear $academicYear): void
    {
        $academicYear->update(['status' => 'closed']);
    }
}