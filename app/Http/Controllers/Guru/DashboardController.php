<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
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

        return Inertia::render('Guru/Dashboard', [
            'activeYear'  => $activeYear?->name,
            'assignments' => $assignments,
            'stats' => [
                'classrooms' => $assignments->pluck('classroom_id')->unique()->count(),
                'subjects'   => $assignments->count(),
            ],
        ]);
    }
}