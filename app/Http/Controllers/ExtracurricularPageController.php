<?php

namespace App\Http\Controllers;

use App\Models\Extracurricular;
use App\Models\PpdbSetting;
use App\Models\SchoolHeroPhoto;
use App\Models\SchoolSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

class ExtracurricularPageController extends Controller
{
    public function __invoke(Request $request)
    {
        $user    = $request->user();
        $role    = $user?->getRoleNames()->first();
        $setting = SchoolSetting::current();

        $school = $setting ? [
            'name'   => $setting->name,
            'tagline'=> $setting->tagline,
            'npsn'   => $setting->npsn,
            'phone'  => $setting->phone,
            'email'  => $setting->email,
            'logo'   => $setting->logo ? Storage::disk('public')->url($setting->logo) : null,
        ] : null;

        $extracurriculars = Extracurricular::active()->ordered()->get()->map(fn ($e) => [
            'id'          => $e->id,
            'name'        => $e->name,
            'description' => $e->description,
            'image'       => $e->image ? Storage::disk('public')->url($e->image) : null,
        ]);

        $ppdb = PpdbSetting::latest()->first();

        return inertia('Ekskul', [
            'school'           => $school,
            'extracurriculars' => $extracurriculars,
            'canLogin'         => Route::has('login'),
            'isLoggedIn'       => $user !== null,
            'dashboardRoute'   => $role ? $this->resolveDashboardRoute($role) : null,
            'ppdbActive'       => $ppdb?->isRegistrationOpen() ?? false,
            'heroPhotos'       => SchoolHeroPhoto::forPage('ekskul')->map(fn ($p) => Storage::disk('public')->url($p->file_path))->values()->all(),
        ]);
    }

    private function resolveDashboardRoute(string $role): string
    {
        return match ($role) {
            'tu_keuangan' => route('keuangan.dashboard'),
            default       => route($role . '.dashboard'),
        };
    }
}
