<?php

namespace App\Http\Controllers;

use App\Models\Extracurricular;
use App\Models\SchoolGallery;
use App\Models\SchoolSetting;
use App\Models\PpdbSetting;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

class WelcomeController extends Controller
{
    private function resolveDashboardRoute(string $role): string
    {
        return match ($role) {
            'tu_keuangan' => route('keuangan.dashboard'),
            default       => route($role . '.dashboard'),
        };
    }

    public function __invoke(Request $request)
    {
        $user    = $request->user();
        $role    = $user?->getRoleNames()->first();
        $setting = SchoolSetting::current();

        $school = $setting ? [
            'name'           => $setting->name,
            'tagline'        => $setting->tagline,
            'npsn'           => $setting->npsn,
            'principal_name' => $setting->principal_name,
            'principal_nip'  => $setting->principal_nip,
            'address'        => $setting->address,
            'phone'          => $setting->phone,
            'email'          => $setting->email,
            'website'        => $setting->website,
            'description'    => $setting->description,
            'vision'         => $setting->vision,
            'mission'        => $setting->mission,
            'history'        => $setting->history,
            'logo'           => $setting->logo ? Storage::disk('public')->url($setting->logo) : null,
        ] : null;

        $extracurriculars = Extracurricular::active()->ordered()->get()->map(fn ($e) => [
            'id'          => $e->id,
            'name'        => $e->name,
            'description' => $e->description,
            'image'       => $e->image ? Storage::disk('public')->url($e->image) : null,
        ]);

        $galleries = SchoolGallery::ordered()->get()->map(fn ($g) => [
            'id'        => $g->id,
            'type'      => $g->type,
            'file_url'  => $g->file_path ? Storage::disk('public')->url($g->file_path) : null,
            'video_url' => $g->video_url,
            'caption'   => $g->caption,
        ]);

        $ppdb = PpdbSetting::latest()->first();

        return inertia('Welcome', [
            'canLogin'         => Route::has('login'),
            'isLoggedIn'       => $user !== null,
            'dashboardRoute'   => $role ? $this->resolveDashboardRoute($role) : null,
            'school'           => $school,
            'extracurriculars' => $extracurriculars,
            'galleries'        => $galleries,
            'ppdbActive'       => $ppdb?->isRegistrationOpen() ?? false,
            'stats'            => [
                'students'         => Student::where('status', 'active')->count(),
                'teachers'         => Teacher::count(),
                'extracurriculars' => Extracurricular::where('is_active', true)->count(),
                'since'            => 1985,
            ],
        ]);
    }
}
