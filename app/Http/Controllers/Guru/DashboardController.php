<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\SchoolPost;
use App\Models\TeacherAttendance;
use App\Services\TeacherAttendanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private TeacherAttendanceService $attendanceService,
    ) {}

    public function __invoke(Request $request): Response
    {
        $teacher    = $request->user()->teacher;
        $activeYear = AcademicYear::where('status', 'active')->first();

        $assignments = collect();

        if ($teacher && $activeYear) {
            $assignments = $teacher->teacherSubjects()
                ->with(['classroom', 'subject'])
                ->where('academic_year_id', $activeYear->id)
                ->get()
                ->map(fn ($ts) => [
                    'classroom_id'   => $ts->classroom_id,
                    'classroom_name' => $ts->classroom->name,
                    'subject_name'   => $ts->subject->name,
                ]);
        }

        // Attendance summary for current month
        $now = Carbon::now();
        $attendanceSummary = $teacher
            ? $this->attendanceService->getMonthlySummary($teacher, $now->month, $now->year)
            : ['hadir' => 0, 'izin' => 0, 'sakit' => 0, 'alpha' => 0, 'total' => 0];

        // Last 7 attendance records
        $recentAttendance = $teacher
            ? TeacherAttendance::where('teacher_id', $teacher->id)
                ->orderBy('date', 'desc')
                ->limit(7)
                ->get()
                ->map(fn ($a) => [
                    'date'   => $a->date->format('Y-m-d'),
                    'status' => $a->status,
                    'notes'  => $a->notes,
                ])
                ->values()
            : collect();

        // Latest 3 published posts
        $latestPosts = SchoolPost::published()
            ->limit(3)
            ->get()
            ->map(fn ($post) => [
                'id'           => $post->id,
                'title'        => $post->title,
                'slug'         => $post->slug,
                'excerpt'      => $post->excerpt,
                'category'     => $post->category,
                'published_at' => $post->published_at?->locale('id')->isoFormat('D MMM YYYY'),
            ]);

        return Inertia::render('Guru/Dashboard', [
            'activeYear'        => $activeYear?->name,
            'assignments'       => $assignments,
            'stats' => [
                'classrooms' => $assignments->pluck('classroom_id')->unique()->count(),
                'subjects'   => $assignments->count(),
            ],
            'attendanceSummary' => $attendanceSummary,
            'recentAttendance'  => $recentAttendance,
            'latestPosts'       => $latestPosts,
        ]);
    }
}