<?php

namespace App\Http\Controllers;

use App\Models\SchoolPost;
use App\Models\SchoolSetting;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

class PublicSchoolPostController extends Controller
{
    private function schoolData(): ?array
    {
        $setting = SchoolSetting::current();

        if (! $setting) {
            return null;
        }

        return [
            'name'    => $setting->name,
            'tagline' => $setting->tagline,
            'npsn'    => $setting->npsn,
            'address' => $setting->address,
            'phone'   => $setting->phone,
            'email'   => $setting->email,
            'website' => $setting->website,
            'logo'    => $setting->logo ? Storage::disk('public')->url($setting->logo) : null,
        ];
    }

    public function index()
    {
        $user   = request()->user();
        $role   = $user?->getRoleNames()->first();

        $posts = SchoolPost::published()
            ->paginate(9)
            ->through(fn ($p) => $this->formatPost($p));

        return inertia('Berita', [
            'posts'          => $posts,
            'school'         => $this->schoolData(),
            'canLogin'       => Route::has('login'),
            'isLoggedIn'     => $user !== null,
            'dashboardRoute' => $role ? $this->resolveDashboardRoute($role) : null,
        ]);
    }

    public function show(SchoolPost $post)
    {
        abort_if(! $post->is_published, 404);

        $user = request()->user();
        $role = $user?->getRoleNames()->first();

        return inertia('BeritaDetail', [
            'post'           => $this->formatPost($post),
            'school'         => $this->schoolData(),
            'canLogin'       => Route::has('login'),
            'isLoggedIn'     => $user !== null,
            'dashboardRoute' => $role ? $this->resolveDashboardRoute($role) : null,
        ]);
    }

    private function formatPost(SchoolPost $post): array
    {
        return [
            'id'           => $post->id,
            'title'        => $post->title,
            'slug'         => $post->slug,
            'excerpt'      => $post->excerpt,
            'content'      => $post->content,
            'cover_image'  => $post->cover_image ? Storage::disk('public')->url($post->cover_image) : null,
            'category'     => $post->category,
            'is_published' => $post->is_published,
            'published_at' => $post->published_at?->locale('id')->isoFormat('D MMMM YYYY'),
        ];
    }

    private function resolveDashboardRoute(string $role): string
    {
        return match ($role) {
            'tu_keuangan' => route('keuangan.dashboard'),
            default       => route($role . '.dashboard'),
        };
    }
}
