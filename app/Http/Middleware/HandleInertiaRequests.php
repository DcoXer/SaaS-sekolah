<?php

namespace App\Http\Middleware;

use App\Services\NotificationService;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user,
                'role' => $user?->getRoleNames()->first(),
            ],
            'flash' => [
                'success'           => $request->session()->get('success'),
                'error'             => $request->session()->get('error'),
                'registered_number' => $request->session()->get('registered_number'),
            ],
            'notifications' => fn () => $user
                ? app(NotificationService::class)->getForUser($user, 30)->map(fn ($n) => [
                    'id'         => $n->id,
                    'type'       => $n->type,
                    'title'      => $n->title,
                    'message'    => $n->message,
                    'data'       => $n->data,
                    'read_at'    => $n->read_at?->toISOString(),
                    'created_at' => $n->created_at->diffForHumans(),
                ])
                : [],
            'unreadCount' => fn () => $user
                ? app(NotificationService::class)->getUnreadCount($user)
                : 0,
            'midtrans' => [
                'client_key'    => config('services.midtrans.client_key'),
                'is_production' => config('services.midtrans.is_production'),
            ],
            'seo' => function () {
                $school = \App\Models\SchoolSetting::current();
                return [
                    'name'        => $school?->name        ?? config('app.name'),
                    'tagline'     => $school?->tagline      ?? '',
                    'description' => $school?->description  ?? '',
                    'address'     => $school?->address      ?? '',
                    'phone'       => $school?->phone        ?? '',
                    'email'       => $school?->email        ?? '',
                    'website'     => $school?->website      ?? config('app.url'),
                    'logo_url'    => $school?->logo         ? asset('storage/' . $school->logo) : null,
                    'npsn'        => $school?->npsn         ?? '',
                ];
            },
        ];
    }
}
