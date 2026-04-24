<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePpdbRegistrationRequest;
use App\Models\PpdbRegistration;
use App\Models\SchoolSetting;
use App\Services\PpdbService;
use Inertia\Inertia;
use Inertia\Response;

class PublicPpdbController extends Controller
{
    public function __construct(private PpdbService $service) {}

    public function index(): Response
    {
        $setting = $this->service->getSetting();
        $school  = SchoolSetting::first();
        $stats   = $setting ? $this->service->getStats($setting) : null;

        return Inertia::render('Ppdb', [
            'setting'     => $setting,
            'school'      => $school,
            'stats'       => $stats,
            'canLogin'    => true,
            'isLoggedIn'  => auth()->check(),
            'dashboardRoute' => auth()->check() ? $this->dashboardRoute() : null,
        ]);
    }

    public function store(StorePpdbRegistrationRequest $request)
    {
        $setting = $this->service->getSetting();

        if (! $setting || ! $setting->isRegistrationOpen()) {
            return redirect()->back()->withErrors(['form' => 'Pendaftaran PPDB saat ini tidak dibuka.']);
        }

        $reg = $this->service->register($setting, $request->validated(), $request->allFiles());

        return redirect()->route('ppdb.index')
            ->with('success', "Pendaftaran berhasil! Nomor pendaftaran Anda: {$reg->registration_number}");
    }

    public function check()
    {
        $number = request('no');
        $result = null;
        $error  = null;

        if ($number) {
            $result = PpdbRegistration::with('ppdbSetting')
                ->where('registration_number', strtoupper(trim($number)))
                ->first();

            if (! $result) {
                $error = 'Nomor pendaftaran tidak ditemukan.';
            }
        }

        $school = SchoolSetting::first();

        return Inertia::render('PpdbCheck', [
            'result'      => $result,
            'error'       => $error,
            'number'      => $number,
            'school'      => $school,
            'canLogin'    => true,
            'isLoggedIn'  => auth()->check(),
            'dashboardRoute' => auth()->check() ? $this->dashboardRoute() : null,
        ]);
    }

    private function dashboardRoute(): string
    {
        $role = auth()->user()?->roles->first()?->name;
        return match ($role) {
            'kamad'       => '/kamad/dashboard',
            'tu_keuangan' => '/keuangan/dashboard',
            'guru'        => '/guru/dashboard',
            'siswa'       => '/siswa/dashboard',
            default       => '/operator/dashboard',
        };
    }
}
