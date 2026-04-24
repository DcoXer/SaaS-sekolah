<?php

namespace App\Services;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TeacherService
{
    public function getAll(): Collection
    {
        return Teacher::with('user')->latest()->get();
    }

    public function getById(Teacher $teacher): Teacher
    {
        return $teacher->load('user');
    }

    public function create(array $data): Teacher
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            $user->assignRole('guru');

            return Teacher::create([
                'user_id' => $user->id,
                'type'    => $data['type'],
                'nip'     => $data['nip'] ?? null,
                'gender'  => $data['gender'],
                'phone'   => $data['phone'] ?? null,
                'photo'   => $data['photo'] ?? null,
            ]);
        });
    }

    public function update(Teacher $teacher, array $data): Teacher
    {
        return DB::transaction(function () use ($teacher, $data) {
            $teacher->user->update([
                'name'  => $data['name'],
                'email' => $data['email'],
            ]);

            if (!empty($data['password'])) {
                $teacher->user->update([
                    'password' => Hash::make($data['password']),
                ]);
            }

            $teacher->update([
                'type'     => $data['type'],
                'position' => $data['position'] ?? null,
                'nip'      => $data['nip'] ?? null,
                'gender'   => $data['gender'],
                'phone'    => $data['phone'] ?? null,
            ]);

            return $teacher->fresh('user');
        });
    }

    public function delete(Teacher $teacher): void
    {
        DB::transaction(function () use ($teacher) {
            $teacher->user->delete();
            $teacher->delete();
        });
    }
}