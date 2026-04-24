<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSchoolGalleryRequest;
use App\Models\SchoolGallery;
use App\Services\SchoolGalleryService;
use Inertia\Inertia;
use Inertia\Response;

class SchoolGalleryController extends Controller
{
    public function __construct(private SchoolGalleryService $service) {}

    public function index(): Response
    {
        return Inertia::render('Operator/SchoolGallery/Index', [
            'galleries' => $this->service->all(),
        ]);
    }

    public function store(StoreSchoolGalleryRequest $request)
    {
        $this->service->store($request->validated());

        return redirect()->back()->with('success', 'Item galeri berhasil ditambahkan.');
    }

    public function destroy(SchoolGallery $schoolGallery)
    {
        $this->service->delete($schoolGallery);

        return redirect()->back()->with('success', 'Item galeri berhasil dihapus.');
    }
}
