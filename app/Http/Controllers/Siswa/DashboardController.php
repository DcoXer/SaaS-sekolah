<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Invoice;
use App\Models\ReportCard;
use App\Models\SchoolPost;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $student    = $request->user()->student;
        $activeYear = AcademicYear::where('status', 'active')->first();

        $unpaidInvoices = collect();

        if ($student) {
            $unpaidInvoices = Invoice::where('student_id', $student->id)
                ->whereIn('status', ['unpaid', 'partial'])
                ->with('paymentType')
                ->orderBy('due_date')
                ->get()
                ->map(fn ($inv) => [
                    'id'           => $inv->id,
                    'amount'       => $inv->amount,
                    'status'       => $inv->status,
                    'due_date'     => $inv->due_date?->translatedFormat('d M Y'),
                    'payment_type' => $inv->paymentType->name ?? '-',
                ]);
        }

        // Current classroom name
        $classroom = null;
        if ($student && $activeYear) {
            $classroom = $student->classrooms()
                ->wherePivot('academic_year_id', $activeYear->id)
                ->first()?->name;
        }

        // Latest report card status
        $reportCard = null;
        if ($student && $activeYear) {
            $rc = ReportCard::where('student_id', $student->id)
                ->where('academic_year_id', $activeYear->id)
                ->latest('semester')
                ->first();
            if ($rc) {
                $reportCard = ['semester' => $rc->semester, 'status' => $rc->status];
            }
        }

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

        return Inertia::render('Siswa/Dashboard', [
            'activeYear' => $activeYear?->name,
            'student'    => $student ? [
                'name' => $student->name,
                'nisn' => $student->nisn,
                'nis'  => $student->nis,
            ] : null,
            'stats' => [
                'unpaid'  => $unpaidInvoices->where('status', 'unpaid')->count(),
                'partial' => $unpaidInvoices->where('status', 'partial')->count(),
            ],
            'unpaidInvoices'  => $unpaidInvoices,
            'classroom'       => $classroom,
            'reportCardStatus' => $reportCard,
            'latestPosts'     => $latestPosts,
        ]);
    }
}
