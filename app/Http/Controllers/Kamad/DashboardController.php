<?php

namespace App\Http\Controllers\Kamad;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Letter;
use App\Models\ReportCard;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $activeYear = AcademicYear::where('status', 'active')->first();

        return Inertia::render('Kamad/Dashboard', [
            'activeYear' => $activeYear?->only(['name']),
            'pending' => [
                'years'   => AcademicYear::where('status', 'pending')->count(),
                'letters' => Letter::where('status', 'waiting_approval')->count(),
                'reports' => ReportCard::where('status', 'draft')->count(),
            ],
        ]);
    }
}