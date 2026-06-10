<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSchoolPostRequest;
use App\Models\SchoolPost;
use App\Models\SchoolPostImage;
use App\Services\SchoolPostService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class SchoolPostController extends Controller
{
    public function __construct(private SchoolPostService $service) {}

    public function create(): Response
    {
        return Inertia::render('Operator/SchoolPost/Create');
    }

    public function edit(SchoolPost $post): Response
    {
        $post->load('images');

        return Inertia::render('Operator/SchoolPost/Edit', [
            'post' => [
                'id'              => $post->id,
                'title'           => $post->title,
                'excerpt'         => $post->excerpt,
                'content'         => $post->content,
                'cover_image_url' => $post->cover_image ? Storage::disk('public')->url($post->cover_image) : null,
                'category'        => $post->category,
                'is_published'    => $post->is_published,
                'images'          => $post->images->map(fn ($img) => [
                    'id'  => $img->id,
                    'url' => Storage::disk('public')->url($img->path),
                ])->values()->all(),
            ],
        ]);
    }

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

        return redirect()->route('operator.school-posts.index')->with('success', 'Post berhasil dibuat.');
    }

    public function update(StoreSchoolPostRequest $request, SchoolPost $post)
    {
        $this->service->update($post, $request->validated());

        return redirect()->route('operator.school-posts.index')->with('success', 'Post berhasil diperbarui.');
    }

    public function destroy(SchoolPost $post)
    {
        $this->service->destroy($post);

        return redirect()->back()->with('success', 'Post berhasil dihapus.');
    }

    public function show(SchoolPost $post): Response
    {
        $post->load('images');

        return Inertia::render('Operator/SchoolPost/Show', [
            'post' => [
                'id'              => $post->id,
                'title'           => $post->title,
                'slug'            => $post->slug,
                'excerpt'         => $post->excerpt,
                'content'         => $post->content,
                'cover_image_url' => $post->cover_image ? Storage::disk('public')->url($post->cover_image) : null,
                'category'        => $post->category,
                'is_published'    => $post->is_published,
                'published_at'    => $post->published_at?->locale('id')->isoFormat('D MMMM YYYY'),
                'images'          => $post->images->map(fn ($img) => [
                    'id'  => $img->id,
                    'url' => Storage::disk('public')->url($img->path),
                ]),
            ],
        ]);
    }

    public function togglePublish(SchoolPost $post)
    {
        $this->service->togglePublish($post);

        $msg = $post->fresh()->is_published ? 'Post berhasil dipublikasikan.' : 'Post berhasil disembunyikan.';

        return redirect()->back()->with('success', $msg);
    }

    public function storeImage(Request $request, SchoolPost $post)
    {
        $request->validate(['image' => 'required|image|mimes:jpg,jpeg,png,webp|max:3072']);

        $this->service->storeImage($post, $request->file('image'));

        return redirect()->back()->with('success', 'Foto berhasil diunggah.');
    }

    public function destroyImage(SchoolPost $post, SchoolPostImage $image)
    {
        abort_if($image->school_post_id !== $post->id, 404);

        $this->service->deleteImage($image);

        return redirect()->back()->with('success', 'Foto berhasil dihapus.');
    }
}
