<?php

namespace App\Services;

use App\Models\Classroom;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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

    public function bulkGenerateAccounts(): array
    {
        $teachers    = Teacher::with('user')->get();
        $credentials = [];
        $chars       = 'ABCDEFGHJKMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789';

        DB::transaction(function () use ($teachers, $chars, &$credentials) {
            foreach ($teachers as $teacher) {
                // Generate password baru
                $password = '';
                for ($i = 0; $i < 10; $i++) {
                    $password .= $chars[random_int(0, strlen($chars) - 1)];
                }

                $email = $teacher->user->email;

                // Fix email placeholder (@sekolah.local dari import CSV)
                if (str_ends_with($email, '@sekolah.local')) {
                    $base     = $teacher->nip
                        ? Str::slug($teacher->nip, '.')
                        : Str::slug($teacher->user->name, '.');
                    $newEmail = $base . '@guru.sekolah.id';
                    $suffix   = 1;
                    while (User::where('email', $newEmail)->where('id', '!=', $teacher->user->id)->exists()) {
                        $newEmail = $base . $suffix . '@guru.sekolah.id';
                        $suffix++;
                    }
                    $email = $newEmail;
                    $teacher->user->update(['email' => $email, 'password' => Hash::make($password)]);
                } else {
                    $teacher->user->update(['password' => Hash::make($password)]);
                }

                $credentials[] = [
                    'nip'      => $teacher->nip ?? '',
                    'name'     => $teacher->user->name,
                    'email'    => $email,
                    'password' => $password,
                ];
            }
        });

        return $credentials;
    }

    public function delete(Teacher $teacher): void
    {
        DB::transaction(function () use ($teacher) {
            // Bersihkan homeroom_teacher_id di semua kelas yang diampu guru ini
            Classroom::where('homeroom_teacher_id', $teacher->id)
                     ->update(['homeroom_teacher_id' => null]);

            $teacher->user->delete();
            $teacher->delete();
        });
    }
}