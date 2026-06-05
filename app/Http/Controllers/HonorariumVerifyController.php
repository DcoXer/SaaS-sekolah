<?php

namespace App\Http\Controllers;

use App\Models\TeacherHonorarium;
use Inertia\Inertia;
use Inertia\Response;

class HonorariumVerifyController extends Controller
{
    public function show(string $code): Response
    {
        $honorarium = TeacherHonorarium::with(['teacher.user', 'academicYear', 'tuKeuangan'])
            ->where('slip_code', $code)
            ->first();

        if (!$honorarium) {
            return Inertia::render('Honor/Verify', [
                'valid'       => false,
                'honorarium'  => null,
            ]);
        }

        return Inertia::render('Honor/Verify', [
            'valid'        => true,
            'honorarium'   => [
                'slip_code'      => $honorarium->slip_code,
                'period_label'   => $honorarium->periodLabel(),
                'status'         => $honorarium->status,
                'total_amount'   => $honorarium->total_amount,
                'teaching_hours' => $honorarium->teaching_hours,
                'transport_days' => $honorarium->transport_days,
                'paid_at'        => $honorarium->paid_at?->translatedFormat('d F Y'),
            ],
            'teacher'      => [
                'name' => $honorarium->teacher->user->name,
                'nip'  => $honorarium->teacher->nip ?? '-',
                'type' => $honorarium->teacher->type === 'guru_kelas' ? 'Guru Kelas' : 'Guru Bidang',
            ],
            'academic_year' => $honorarium->academicYear->name,
            'processed_by'  => $honorarium->tuKeuangan?->name,
        ]);
    }
}
