<?php

namespace App\Services;

use App\Models\SchoolPost;
use App\Models\SchoolPostImage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;

class SchoolPostService
{
    public function index(array $filters): LengthAwarePaginator
    {
        return SchoolPost::query()
            ->when($filters['search'] ?? null, fn ($q, $s) => $q->where('title', 'like', "%{$s}%"))
            ->when($filters['category'] ?? null, fn ($q, $c) => $q->where('category', $c))
            ->when(isset($filters['status']) && $filters['status'] !== '', function ($q) use ($filters) {
                if ($filters['status'] === 'published') {
                    $q->where('is_published', true);
                } elseif ($filters['status'] === 'draft') {
                    $q->where('is_published', false);
                }
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();
    }

    public function store(array $data): SchoolPost
    {
        $data['slug']        = $this->generateSlug($data['title']);
        $data['is_published'] = $data['is_published'] ?? false;

        if (!empty($data['cover_image']) && is_object($data['cover_image'])) {
            $data['cover_image'] = $data['cover_image']->store('school-posts', 'public');
        } else {
            $data['cover_image'] = null;
        }

        if ($data['is_published'] && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        return SchoolPost::create($data);
    }

    public function update(SchoolPost $post, array $data): SchoolPost
    {
        $data['is_published'] = $data['is_published'] ?? false;

        if (!empty($data['cover_image']) && is_object($data['cover_image'])) {
            if ($post->cover_image) {
                Storage::disk('public')->delete($post->cover_image);
            }
            $data['cover_image'] = $data['cover_image']->store('school-posts', 'public');
        } else {
            unset($data['cover_image']);
        }

        if ($data['is_published'] && ! $post->published_at) {
            $data['published_at'] = now();
        } elseif (! $data['is_published']) {
            $data['published_at'] = null;
        }

        $post->update($data);

        return $post;
    }

    public function destroy(SchoolPost $post): void
    {
        if ($post->cover_image) {
            Storage::disk('public')->delete($post->cover_image);
        }

        $post->delete();
    }

    public function togglePublish(SchoolPost $post): SchoolPost
    {
        if ($post->is_published) {
            $post->update(['is_published' => false, 'published_at' => null]);
        } else {
            $post->update([
                'is_published' => true,
                'published_at' => $post->published_at ?? now(),
            ]);
        }

        return $post;
    }

    public function storeImage(SchoolPost $post, UploadedFile $file): SchoolPostImage
    {
        $path = $file->store('school-posts/images', 'public');
        $lastOrder = $post->images()->max('sort_order') ?? 0;

        return $post->images()->create([
            'path'       => $path,
            'sort_order' => $lastOrder + 1,
        ]);
    }

    public function deleteImage(SchoolPostImage $image): void
    {
        Storage::disk('public')->delete($image->path);
        $image->delete();
    }

    private function generateSlug(string $title): string
    {
        $slug = Str::slug($title);
        $base = $slug;
        $i    = 2;

        while (SchoolPost::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
