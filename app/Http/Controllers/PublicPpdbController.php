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
            'setting'        => $setting,
            'school'         => $school,
            'stats'          => $stats,
            'serverDate'     => now()->toDateString(),
            'canLogin'       => true,
            'isLoggedIn'     => auth()->check(),
            'dashboardRoute' => auth()->check() ? $this->dashboardRoute() : null,
        ]);
    }

    public function create(): Response
    {
        $setting = $this->service->getSetting();
        $school  = SchoolSetting::first();

        return Inertia::render('PpdbDaftar', [
            'setting'        => $setting,
            'school'         => $school,
            'serverDate'     => now()->toDateString(),
            'canLogin'       => true,
            'isLoggedIn'     => auth()->check(),
            'dashboardRoute' => auth()->check() ? $this->dashboardRoute() : null,
        ]);
    }

    public function store(StorePpdbRegistrationRequest $request)
    {
        $setting = $this->service->getSetting();

        if (! $setting || ! $setting->isRegistrationOpen()) {
            return redirect()->back()->withErrors(['form' => 'Pendaftaran PPDB saat ini tidak dibuka.']);
        }

        try {
            $reg = $this->service->register($setting, $request->validated(), $request->allFiles());
        } catch (\RuntimeException $e) {
            return redirect()->back()->withErrors(['form' => $e->getMessage()]);
        }

        return redirect()->route('ppdb.index')
            ->with('registered_number', $reg->registration_number);
    }

    public function check()
    {
        $number = request('no');
        $result = null;
        $error  = null;
        $invoice = null;

        if ($number) {
            $reg = PpdbRegistration::with('ppdbSetting')
                ->where('registration_number', strtoupper(trim($number)))
                ->first();

            if (! $reg) {
                $error = 'Nomor pendaftaran tidak ditemukan.';
            } else {
                // Hanya expose field yang ditampilkan di halaman publik.
                // NIK, nomor KK, NIK orang tua, file dokumen TIDAK di-expose.
                $result = [
                    'registration_number' => $reg->registration_number,
                    'full_name'           => $reg->full_name,
                    'gender'              => $reg->gender,
                    'birth_place'         => $reg->birth_place,
                    'birth_date'          => $reg->birth_date,
                    'parent_name'         => $reg->parent_name,
                    'parent_phone'        => $reg->parent_phone,
                    'status'              => $reg->status,
                    'notes'               => $reg->status === 'rejected' ? $reg->notes : null,
                    'created_at'          => $reg->created_at,
                ];

                if ($reg->status === 'accepted') {
                    $invoice = $reg->invoice;
                    if ($invoice) {
                        $invoice->append(['total_paid', 'remaining_amount']);
                    }
                }
            }
        }

        $school = SchoolSetting::first();

        return Inertia::render('PpdbCheck', [
            'result'         => $result,
            'invoice'        => $invoice,
            'error'          => $error,
            'number'         => $number,
            'school'         => $school,
            'canLogin'       => true,
            'isLoggedIn'     => auth()->check(),
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
