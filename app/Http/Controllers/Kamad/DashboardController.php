<?php

namespace App\Http\Controllers\Kamad;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Letter;
use App\Models\PpdbRegistration;
use App\Models\ReportCard;
use App\Models\TeacherAttendance;
use App\Models\TeacherHonorarium;
use Carbon\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $activeYear = AcademicYear::where('status', 'active')->first();

        // PPDB stats
        $ppdb = [
            'pending'  => PpdbRegistration::where('status', 'pending')->count(),
            'accepted' => PpdbRegistration::where('status', 'accepted')->count(),
            'total'    => PpdbRegistration::count(),
        ];

        // Honorarium: draft = belum dibayar
        $honorarium = [
            'unpaid' => TeacherHonorarium::where('status', 'draft')->count(),
        ];

        // Absensi bulan ini
        $now = Carbon::now();
        $attendanceThisMonth = TeacherAttendance::whereYear('date', $now->year)
            ->whereMonth('date', $now->month)
            ->selectRaw("status, COUNT(*) as total")
            ->groupBy('status')
            ->pluck('total', 'status');

        $attendance = [
            'hadir' => $attendanceThisMonth->get('hadir', 0),
            'izin'  => $attendanceThisMonth->get('izin', 0),
            'sakit' => $attendanceThisMonth->get('sakit', 0),
            'alpha' => $attendanceThisMonth->get('alpha', 0),
            'month' => $now->translatedFormat('F Y'),
        ];

        return Inertia::render('Kamad/Dashboard', [
            'activeYear' => $activeYear?->only(['name']),
            'pending' => [
                'years'   => AcademicYear::where('status', 'pending')->count(),
                'letters' => Letter::where('status', 'waiting_approval')->count(),
                'reports' => ReportCard::where('status', 'draft')->count(),
            ],
            'ppdb'        => $ppdb,
            'honorarium'  => $honorarium,
            'attendance'  => $attendance,
        ]);
    }
}