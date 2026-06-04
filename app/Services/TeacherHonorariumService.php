<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Teacher;
use App\Models\TeacherHonorarium;
use App\Models\TeacherTeachingHour;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class TeacherHonorariumService
{
    public function __construct(private TeacherAttendanceService $attendanceService) {}

    public function getAll(array $filters = []): Collection
    {
        $query = TeacherHonorarium::with(['teacher.user', 'academicYear', 'tuKeuangan'])
            ->latest();

        if (!empty($filters['month'])) {
            $query->where('period_month', $filters['month']);
        }

        if (!empty($filters['year'])) {
            $query->where('period_year', $filters['year']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['teacher_id'])) {
            $query->where('teacher_id', $filters['teacher_id']);
        }

        return $query->get();
    }

    public function generate(Teacher $teacher, AcademicYear $academicYear, int $month, int $year): TeacherHonorarium
    {
        // Pastikan guru masih punya akun user aktif
        abort_if(!$teacher->user, 422, 'Akun guru tidak ditemukan, slip tidak dapat dibuat.');

        // Ambil konfigurasi jam pelajaran
        $teachingHour = TeacherTeachingHour::where('teacher_id', $teacher->id)
            ->where('academic_year_id', $academicYear->id)
            ->firstOrFail();

        // Hitung hari hadir di bulan tersebut
        $transportDays = $this->attendanceService->countHadirInMonth($teacher, $month, $year);

        // Kalkulasi
        $teachingHoursAmount = $teachingHour->total_hours * $teachingHour->hourly_rate;
        $transportAmount     = $transportDays * $teachingHour->daily_transport_rate;
        $positionAllowance   = $teachingHour->position_allowance ?? 0;
        $totalAmount         = $teachingHoursAmount + $transportAmount + $positionAllowance;

        return TeacherHonorarium::create([
            'teacher_id'           => $teacher->id,
            'academic_year_id'     => $academicYear->id,
            'period_month'         => $month,
            'period_year'          => $year,
            // snapshot
            'teaching_hours'       => $teachingHour->total_hours,
            'hourly_rate'          => $teachingHour->hourly_rate,
            'transport_days'       => $transportDays,
            'daily_transport_rate' => $teachingHour->daily_transport_rate,
            'position_name'        => $teachingHour->position_name,
            'position_allowance'   => $positionAllowance,
            // kalkulasi
            'teaching_hours_amount' => $teachingHoursAmount,
            'transport_amount'      => $transportAmount,
            'total_amount'          => $totalAmount,
            'status'               => 'draft',
            'slip_code'            => Str::uuid()->toString(),
        ]);
    }

    public function markPaid(TeacherHonorarium $honorarium, User $paidBy): TeacherHonorarium
    {
        $honorarium->update([
            'status'        => 'paid',
            'paid_at'       => now(),
            'tu_keuangan_id' => $paidBy->id,
        ]);

        return $honorarium->fresh(['teacher.user', 'academicYear', 'tuKeuangan']);
    }

    public function markAllPaid(int $month, int $year, User $paidBy): int
    {
        $honorariums = TeacherHonorarium::where('period_month', $month)
            ->where('period_year', $year)
            ->where('status', 'draft')
            ->get();

        foreach ($honorariums as $honorarium) {
            $honorarium->update([
                'status'         => 'paid',
                'paid_at'        => now(),
                'tu_keuangan_id' => $paidBy->id,
            ]);
        }

        return $honorariums->count();
    }

    public function delete(TeacherHonorarium $honorarium): void
    {
        $honorarium->delete();
    }

    public function alreadyGenerated(Teacher $teacher, int $month, int $year): bool
    {
        return TeacherHonorarium::where('teacher_id', $teacher->id)
            ->where('period_month', $month)
            ->where('period_year', $year)
            ->exists();
    }

    /**
     * Generate slip untuk semua guru yang punya konfigurasi jam pelajaran di tahun ajaran tsb.
     * Skip guru yang sudah punya slip untuk periode yang sama.
     * Return ['created' => int, 'skipped' => int]
     */
    public function generateAll(AcademicYear $academicYear, int $month, int $year): array
    {
        $teachers = Teacher::with('user')
            ->whereHas('teachingHours', fn($q) => $q->where('academic_year_id', $academicYear->id))
            ->get();

        $created = 0;
        $skipped = 0;

        foreach ($teachers as $teacher) {
            if ($this->alreadyGenerated($teacher, $month, $year)) {
                $skipped++;
                continue;
            }

            $this->generate($teacher, $academicYear, $month, $year);
            $created++;
        }

        return ['created' => $created, 'skipped' => $skipped];
    }
}
