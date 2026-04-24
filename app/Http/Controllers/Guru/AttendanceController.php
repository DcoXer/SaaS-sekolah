<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTeacherAttendanceRequest;
use App\Http\Requests\UpdateTeacherAttendanceRequest;
use App\Models\TeacherAttendance;
use App\Services\TeacherAttendanceService;
use Inertia\Inertia;
use Inertia\Response;

class AttendanceController extends Controller
{
    public function __construct(private TeacherAttendanceService $service) {}

    public function index(): Response
    {
        $teacher = auth()->user()->teacher;
        abort_if(!$teacher, 403);

        $month = (int) request('month', now()->month);
        $year  = (int) request('year', now()->year);

        $calendar = $this->service->getCalendar($teacher, $month, $year);
        $summary  = $this->service->getMonthlySummary($teacher, $month, $year);

        return Inertia::render('Guru/Attendance/Index', [
            'calendar'     => array_values($calendar),
            'summary'      => $summary,
            'currentMonth' => $month,
            'currentYear'  => $year,
        ]);
    }

    public function store(StoreTeacherAttendanceRequest $request)
    {
        $teacher = auth()->user()->teacher;
        abort_if(!$teacher, 403);

        $existing = $this->service->getByDate($teacher, $request->date);

        if ($existing) {
            return redirect()->back()->withErrors(['date' => 'Absensi untuk tanggal ini sudah ada.']);
        }

        $this->service->store($teacher, $request->validated());

        return redirect()->back()->with('success', 'Absensi berhasil disimpan.');
    }

    public function update(UpdateTeacherAttendanceRequest $request, TeacherAttendance $attendance)
    {
        abort_if($attendance->teacher_id !== auth()->user()->teacher?->id, 403);

        $this->service->update($attendance, $request->validated());

        return redirect()->back()->with('success', 'Absensi berhasil diperbarui.');
    }

    public function destroy(TeacherAttendance $attendance)
    {
        abort_if($attendance->teacher_id !== auth()->user()->teacher?->id, 403);

        $this->service->delete($attendance);

        return redirect()->back()->with('success', 'Absensi berhasil dihapus.');
    }
}
