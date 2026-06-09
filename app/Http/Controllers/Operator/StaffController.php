<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class StaffController extends Controller
{
    public function index()
    {
        $staff = User::role(['kamad', 'tu_keuangan'])
            ->select('id', 'name', 'email', 'created_at')
            ->with('roles:name')
            ->latest()
            ->get()
            ->map(fn($u) => [
                'id'         => $u->id,
                'name'       => $u->name,
                'email'      => $u->email,
                'role'       => $u->roles->first()?->name,
                'created_at' => $u->created_at,
            ]);

        return Inertia::render('Operator/Staff/Index', [
            'staff' => $staff,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role'     => ['required', 'in:kamad,tu_keuangan'],
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);
        $user->assignRole($validated['role']);

        return redirect()->back()->with('success', 'Akun staff berhasil dibuat.');
    }

    public function update(Request $request, User $user)
    {
        abort_unless($user->hasAnyRole(['kamad', 'tu_keuangan']), 403);

        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'email'    => ['required', 'email', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $user->update([
            'name'  => $validated['name'],
            'email' => $validated['email'],
        ]);

        if (!empty($validated['password'])) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }

        return redirect()->back()->with('success', 'Data staff berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        abort_unless($user->hasAnyRole(['kamad', 'tu_keuangan']), 403);
        $user->delete();
        return redirect()->back()->with('success', 'Akun staff berhasil dihapus.');
    }
}
