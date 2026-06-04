<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\SchoolHeroPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SchoolHeroPhotoController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'page'  => ['required', 'in:welcome,tentang,galeri,ekskul'],
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
        ]);

        $path = $request->file('photo')->store("school/hero/{$request->page}", 'public');

        SchoolHeroPhoto::create([
            'page'      => $request->page,
            'file_path' => $path,
            'order'     => 0,
        ]);

        return redirect()->back()->with('success', 'Foto hero berhasil ditambahkan.');
    }

    public function destroy(SchoolHeroPhoto $heroPhoto)
    {
        Storage::disk('public')->delete($heroPhoto->file_path);
        $heroPhoto->delete();

        return redirect()->back()->with('success', 'Foto hero berhasil dihapus.');
    }
}
