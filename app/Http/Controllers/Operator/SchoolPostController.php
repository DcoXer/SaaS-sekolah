<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSchoolPostRequest;
use App\Models\SchoolPost;
use App\Services\SchoolPostService;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class SchoolPostController extends Controller
{
    public function __construct(private SchoolPostService $service) {}

    public function index(): Response
    {
        $filters = request()->only(['search', 'category', 'status']);

        $posts = $this->service->index($filters)->through(fn ($p) => [
            'id'             => $p->id,
            'title'          => $p->title,
            'slug'           => $p->slug,
            'excerpt'        => $p->excerpt,
            'content'        => $p->content,
            'cover_image_url'=> $p->cover_image ? Storage::disk('public')->url($p->cover_image) : null,
            'category'       => $p->category,
            'is_published'   => $p->is_published,
            'published_at'   => $p->published_at?->locale('id')->isoFormat('D MMMM YYYY'),
        ]);

        return Inertia::render('Operator/SchoolPost/Index', [
            'posts'   => $posts,
            'filters' => $filters,
        ]);
    }

    public function store(StoreSchoolPostRequest $request)
    {
        $this->service->store($request->validated());

        return redirect()->back()->with('success', 'Post berhasil dibuat.');
    }

    public function update(StoreSchoolPostRequest $request, SchoolPost $post)
    {
        $this->service->update($post, $request->validated());

        return redirect()->back()->with('success', 'Post berhasil diperbarui.');
    }

    public function destroy(SchoolPost $post)
    {
        $this->service->destroy($post);

        return redirect()->back()->with('success', 'Post berhasil dihapus.');
    }

    public function togglePublish(SchoolPost $post)
    {
        $this->service->togglePublish($post);

        $msg = $post->fresh()->is_published ? 'Post berhasil dipublikasikan.' : 'Post berhasil disembunyikan.';

        return redirect()->back()->with('success', $msg);
    }
}
