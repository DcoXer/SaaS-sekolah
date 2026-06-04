<?php

namespace App\Http\Controllers;

use App\Models\Extracurricular;
use App\Models\PpdbSetting;
use App\Models\SchoolHeroPhoto;
use App\Models\SchoolSetting;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

class AboutPageController extends Controller
{
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
            'logo'           => $setting->logo  ? Storage::disk('public')->url($setting->logo)  : null,
            'stamp'          => $setting->stamp ? Storage::disk('public')->url($setting->stamp) : null,
        ] : null;

        $mapTeacher = fn (Teacher $t) => [
            'id'    => $t->id,
            'name'  => $t->user->name,
            'nip'   => $t->nip,
            'photo' => $t->photo ? Storage::disk('public')->url($t->photo) : null,
        ];

        $mapUser = fn (User $u) => [
            'id'    => $u->id,
            'name'  => $u->name,
            'photo' => $u->avatar ? Storage::disk('public')->url($u->avatar) : null,
        ];

        $allTeachers = Teacher::with('user')->get();

        $wakamad    = $allTeachers->whereIn('position', ['wakamad_kesiswaan', 'wakamad_kurikulum'])
                                  ->sortBy('position')->values()->map($mapTeacher);
        $guruKelas  = $allTeachers->where('type', 'guru_kelas')->values()->map($mapTeacher);
        $guruBidang = $allTeachers->where('type', 'guru_bidang')->values()->map($mapTeacher);
        $tuKeuangan = User::role('tu_keuangan')->get()->map($mapUser);
        $operators  = User::role('operator')->get()->map($mapUser);

        $ppdb = PpdbSetting::latest()->first();

        return inertia('Tentang', [
            'school'         => $school,
            'canLogin'       => Route::has('login'),
            'isLoggedIn'     => $user !== null,
            'dashboardRoute' => $role ? $this->resolveDashboardRoute($role) : null,
            'ppdbActive'     => $ppdb?->isRegistrationOpen() ?? false,
            'heroPhotos'     => SchoolHeroPhoto::forPage('tentang')->map(fn ($p) => Storage::disk('public')->url($p->file_path))->values()->all(),
            'stats'  => [
                'students'         => Student::where('status', 'active')->count(),
                'teachers'         => Teacher::count(),
                'extracurriculars' => Extracurricular::where('is_active', true)->count(),
                'since'            => 1985,
            ],
            'structure' => [
                'wakamad'     => $wakamad,
                'guru_kelas'  => $guruKelas,
                'guru_bidang' => $guruBidang,
                'tu_keuangan' => $tuKeuangan,
                'operators'   => $operators,
            ],
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
