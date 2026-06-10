<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'status' => session('status'),
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('success', 'Informasi profil berhasil disimpan.');
    }

    public function updateAvatar(Request $request): RedirectResponse
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $user = $request->user();

        // Hapus avatar lama
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $path = $request->file('avatar')->store('avatars', 'public');

        $user->update(['avatar' => $path]);

        return Redirect::route('profile.edit')->with('success', 'Foto profil berhasil diperbarui.');
    }

    public function updateSignature(Request $request): RedirectResponse
    {
        $request->validate([
            'signature' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $user = $request->user();

        if ($user->signature) {
            Storage::disk('public')->delete($user->signature);
        }

        $path = $request->file('signature')->store('signatures', 'public');

        $user->update(['signature' => $path]);

        return Redirect::route('profile.edit')->with('success', 'Tanda tangan berhasil disimpan.');
    }

    public function deleteSignature(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->signature) {
            Storage::disk('public')->delete($user->signature);
            $user->update(['signature' => null]);
        }

        return Redirect::route('profile.edit')->with('success', 'Tanda tangan berhasil dihapus.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'string', 'min:8', 'max:100', 'confirmed'],
        ]);

        $request->user()->update([
            'password' => $request->password,
        ]);

        return Redirect::route('profile.edit')->with('success', 'Password berhasil diubah.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
