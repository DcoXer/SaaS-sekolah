<?php

namespace App\Services;

use App\Models\Teacher;
use App\Models\TeacherAttendance;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class TeacherAttendanceService
{
    public function getMonthly(Teacher $teacher, int $month, int $year): Collection
    {
        return TeacherAttendance::where('teacher_id', $teacher->id)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->orderBy('date')
            ->get();
    }

    public function getMonthlySummary(Teacher $teacher, int $month, int $year): array
    {
        $records = $this->getMonthly($teacher, $month, $year);

        return [
            'hadir' => $records->where('status', 'hadir')->count(),
            'izin'  => $records->where('status', 'izin')->count(),
            'sakit' => $records->where('status', 'sakit')->count(),
            'alpha' => $records->where('status', 'alpha')->count(),
            'total' => $records->count(),
        ];
    }

    public function getByDate(Teacher $teacher, string $date): ?TeacherAttendance
    {
        return TeacherAttendance::where('teacher_id', $teacher->id)
            ->where('date', $date)
            ->first();
    }

    public function store(Teacher $teacher, array $data): TeacherAttendance
    {
        return TeacherAttendance::create([
            'teacher_id' => $teacher->id,
            'date'       => $data['date'],
            'status'     => $data['status'],
            'notes'      => $data['notes'] ?? null,
            'latitude'   => $data['latitude'] ?? null,
            'longitude'  => $data['longitude'] ?? null,
        ]);
    }

    public function update(TeacherAttendance $attendance, array $data): TeacherAttendance
    {
        $attendance->update([
            'status' => $data['status'],
            'notes'  => $data['notes'] ?? null,
        ]);

        return $attendance->fresh();
    }

    public function delete(TeacherAttendance $attendance): void
    {
        $attendance->delete();
    }

    public function countHadirInMonth(Teacher $teacher, int $month, int $year): int
    {
        return TeacherAttendance::where('teacher_id', $teacher->id)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->where('status', 'hadir')
            ->count();
    }

    public function getCalendar(Teacher $teacher, int $month, int $year): array
    {
        $records = $this->getMonthly($teacher, $month, $year)
            ->keyBy(fn($r) => $r->date->format('Y-m-d'));

        $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;
        $calendar    = [];

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date          = Carbon::createFromDate($year, $month, $day);
            $key           = $date->format('Y-m-d');
            $calendar[$key] = [
                'date'       => $key,
                'day'        => $day,
                'day_name'   => $date->locale('id')->isoFormat('dddd'),
                'is_weekend' => $date->isWeekend(),
                'attendance' => $records->get($key),
            ];
        }

        return $calendar;
    }
}
