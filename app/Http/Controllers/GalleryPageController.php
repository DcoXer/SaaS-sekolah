<?php

namespace App\Http\Controllers;

use App\Models\PpdbSetting;
use App\Models\SchoolGallery;
use App\Models\SchoolHeroPhoto;
use App\Models\SchoolSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

class GalleryPageController extends Controller
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

        $galleries = SchoolGallery::ordered()->get()->map(fn ($g) => [
            'id'        => $g->id,
            'type'      => $g->type,
            'file_url'  => $g->file_path ? Storage::disk('public')->url($g->file_path) : null,
            'video_url' => $g->video_url,
            'caption'   => $g->caption,
        ]);

        $ppdb = PpdbSetting::latest()->first();

        return inertia('Galeri', [
            'school'         => $school,
            'galleries'      => $galleries,
            'canLogin'       => Route::has('login'),
            'isLoggedIn'     => $user !== null,
            'dashboardRoute' => $role ? $this->resolveDashboardRoute($role) : null,
            'ppdbActive'     => $ppdb?->isRegistrationOpen() ?? false,
            'heroPhotos'     => SchoolHeroPhoto::forPage('galeri')->map(fn ($p) => Storage::disk('public')->url($p->file_path))->values()->all(),
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
