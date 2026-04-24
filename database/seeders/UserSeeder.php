<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name'     => 'Kepala Madrasah',
                'email'    => 'kamad@sekolah.test',
                'password' => Hash::make('password'),
                'role'     => 'kamad',
            ],
            [
                'name'     => 'Operator Sekolah',
                'email'    => 'operator@sekolah.test',
                'password' => Hash::make('password'),
                'role'     => 'operator',
            ],
            [
                'name'     => 'TU Keuangan',
                'email'    => 'keuangan@sekolah.test',
                'password' => Hash::make('password'),
                'role'     => 'tu_keuangan',
            ],
            [
                'name'     => 'Guru',
                'email'    => 'guru@sekolah.test',
                'password' => Hash::make('password'),
                'role'     => 'guru',
            ],
            [
                'name'     => 'Wali Murid',
                'email'    => 'siswa@sekolah.test',
                'password' => Hash::make('password'),
                'role'     => 'siswa',
            ],
        ];

        foreach ($users as $data) {
            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => $data['password'],
            ]);

            $user->assignRole($data['role']);
        }
    }
}