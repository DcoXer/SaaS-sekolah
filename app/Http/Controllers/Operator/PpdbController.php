<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePpdbSettingRequest;
use App\Models\PpdbRegistration;
use App\Services\PpdbService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PpdbController extends Controller
{
    public function __construct(private PpdbService $service) {}

    public function index(Request $request): Response
    {
        $setting = $this->service->getSetting();
        $registrations = null;
        $stats = null;

        if ($setting) {
            $registrations = $this->service->getRegistrations($setting, $request->only('search', 'status'));
            $stats         = $this->service->getStats($setting);
        }

        return Inertia::render('Operator/Ppdb/Index', [
            'setting'       => $setting,
            'registrations' => $registrations,
            'stats'         => $stats,
            'filters'       => $request->only('search', 'status'),
        ]);
    }

    public function saveSetting(StorePpdbSettingRequest $request)
    {
        $this->service->upsertSetting($request->validated());
        return redirect()->back()->with('success', 'Pengaturan PPDB berhasil disimpan.');
    }

    public function accept(PpdbRegistration $registration)
    {
        try {
            $this->service->accept($registration, auth()->id());
            return redirect()->back()->with('success', 'Pendaftar berhasil diterima.');
        } catch (\RuntimeException $e) {
            return redirect()->back()->withErrors(['action' => $e->getMessage()]);
        }
    }

    public function reject(Request $request, PpdbRegistration $registration)
    {
        $request->validate(['notes' => 'required|string|max:500']);

        try {
            $this->service->reject($registration, auth()->id(), $request->notes);
            return redirect()->back()->with('success', 'Pendaftar ditolak.');
        } catch (\RuntimeException $e) {
            return redirect()->back()->withErrors(['action' => $e->getMessage()]);
        }
    }

    public function waitlist(PpdbRegistration $registration)
    {
        try {
            $this->service->waitlist($registration, auth()->id());
            return redirect()->back()->with('success', 'Pendaftar dimasukkan ke daftar tunggu.');
        } catch (\RuntimeException $e) {
            return redirect()->back()->withErrors(['action' => $e->getMessage()]);
        }
    }
}
