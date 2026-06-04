<?php

namespace App\Http\Controllers;

use App\Models\SchoolSetting;
use Illuminate\Http\JsonResponse;

class ManifestController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $school = SchoolSetting::current();

        $defaultIcons = [
            ['src' => '/icons/icon-192x192.png', 'sizes' => '192x192', 'type' => 'image/png'],
            ['src' => '/icons/icon-512x512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any maskable'],
        ];

        $icons = $defaultIcons;

        if ($school?->logo) {
            $logoUrl = asset('storage/' . $school->logo);
            $icons   = [
                ['src' => $logoUrl, 'sizes' => 'any', 'type' => $this->mimeFromPath($school->logo), 'purpose' => 'any'],
                ['src' => '/icons/icon-192x192.png', 'sizes' => '192x192', 'type' => 'image/png'],
                ['src' => '/icons/icon-512x512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable'],
            ];
        }

        $manifest = [
            'name'             => $school?->name ?? config('app.name'),
            'short_name'       => $school?->name ? str($school->name)->words(2)->toString() : 'Sekolah',
            'description'      => $school?->description ?? 'Sistem Manajemen Sekolah SD/MI',
            'theme_color'      => '#10b981',
            'background_color' => '#f1f5f9',
            'display'          => 'standalone',
            'orientation'      => 'portrait-primary',
            'start_url'        => '/',
            'scope'            => '/',
            'icons'            => $icons,
        ];

        return response()->json($manifest)
            ->header('Content-Type', 'application/manifest+json')
            ->header('Cache-Control', 'no-cache, must-revalidate');
    }

    private function mimeFromPath(string $path): string
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        return match ($ext) {
            'png'        => 'image/png',
            'jpg', 'jpeg'=> 'image/jpeg',
            'webp'       => 'image/webp',
            'svg'        => 'image/svg+xml',
            default      => 'image/png',
        };
    }
}
