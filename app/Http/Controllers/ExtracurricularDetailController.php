<?php

namespace App\Http\Controllers;

use App\Models\Extracurricular;
use App\Models\PpdbSetting;
use App\Models\SchoolHeroPhoto;
use App\Models\SchoolSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

class ExtracurricularDetailController extends Controller
{
    public function __invoke(Request $request, Extracurricular $extracurricular)
    {
        if (! $extracurricular->is_active) {
            abort(404);
        }

        $extracurricular->load(['achievements', 'photos']);

        $user    = $request->user();
        $role    = $user?->getRoleNames()->first();
        $setting = SchoolSetting::current();

        $school = $setting ? [
            'name'    => $setting->name,
            'tagline' => $setting->tagline,
            'npsn'    => $setting->npsn,
            'phone'   => $setting->phone,
            'email'   => $setting->email,
            'logo'    => $setting->logo ? Storage::disk('public')->url($setting->logo) : null,
        ] : null;

        $ppdb = PpdbSetting::latest()->first();

        return inertia('EkskulDetail', [
            'extracurricular' => [
                'id'           => $extracurricular->id,
                'name'         => $extracurricular->name,
                'description'  => $extracurricular->description,
                'coach'        => $extracurricular->coach,
                'schedule'     => $extracurricular->schedule,
                'image'        => $extracurricular->image ? Storage::disk('public')->url($extracurricular->image) : null,
                'achievements' => $extracurricular->achievements->map(fn ($a) => [
                    'id'         => $a->id,
                    'title'      => $a->title,
                    'year'       => $a->year,
                    'level'      => $a->level,
                    'rank'       => $a->rank,
                    'sort_order' => $a->sort_order,
                ])->values()->all(),
                'photos' => $extracurricular->photos->map(fn ($p) => [
                    'id'  => $p->id,
                    'url' => Storage::disk('public')->url($p->path),
                ])->values()->all(),
            ],
            'school'         => $school,
            'canLogin'       => Route::has('login'),
            'isLoggedIn'     => $user !== null,
            'dashboardRoute' => $role ? $this->resolveDashboardRoute($role) : null,
            'ppdbActive'     => $ppdb?->isRegistrationOpen() ?? false,
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
