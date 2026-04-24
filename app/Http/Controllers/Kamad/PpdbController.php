<?php

namespace App\Http\Controllers\Kamad;

use App\Http\Controllers\Controller;
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

        return Inertia::render('Kamad/Ppdb/Index', [
            'setting'       => $setting,
            'registrations' => $registrations,
            'stats'         => $stats,
            'filters'       => $request->only('search', 'status'),
        ]);
    }
}
