<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\Classroom;
use App\Models\Subject;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $activeYear = AcademicYear::where('status', 'active')->first();

        $studentsByGrade = collect(range(1, 6))->map(fn ($g) => [
            'grade' => "Kelas $g",
            'total' => Student::where('status', 'active')->where('grade', $g)->count(),
        ]);

        return Inertia::render('Operator/Dashboard', [
            'stats' => [
                'teachers'   => Teacher::count(),
                'students'   => Student::where('status', 'active')->count(),
                'classrooms' => $activeYear
                    ? Classroom::where('academic_year_id', $activeYear->id)->count()
                    : 0,
                'subjects'   => Subject::count(),
            ],
            'studentsByGrade' => $studentsByGrade,
            'activeYear'      => $activeYear?->only(['id', 'name']),
            'pendingYear'     => AcademicYear::where('status', 'pending')->first()?->only(['id', 'name']),
        ]);
    }
}
