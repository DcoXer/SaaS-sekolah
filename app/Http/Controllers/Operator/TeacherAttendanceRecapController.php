<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Services\TeacherAttendanceService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TeacherAttendanceRecapController extends Controller
{
    public function __construct(
        private TeacherAttendanceService $service,
    ) {}

    public function __invoke(Request $request): Response
    {
        $month = (int) $request->input('month', now()->month);
        $year  = (int) $request->input('year',  now()->year);

        $recap = $this->service->getAllMonthlyRecap($month, $year);

        return Inertia::render('Operator/TeacherAttendance/Recap', [
            'recap'     => $recap,
            'month'     => $month,
            'year'      => $year,
        ]);
    }
}
