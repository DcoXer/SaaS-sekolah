<?php

namespace Database\Seeders;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TeacherSeeder extends Seeder
{
    public function run(): void
    {
        $teachers = [
            // ── Guru Kelas 1–3 (satu kelas, semua mapel, otomatis wali kelas) ──
            [
                'name'   => 'Ahmad Fauzi, S.Pd',
                'email'  => 'ahmad.fauzi@sekolah.test',
                'nip'    => '198501012010011001',
                'type'   => 'guru_kelas',
                'gender' => 'L',
                'phone'  => '081234567801',
            ],
            [
                'name'   => 'Siti Rahmawati, S.Pd',
                'email'  => 'siti.rahmawati@sekolah.test',
                'nip'    => '198602022011012001',
                'type'   => 'guru_kelas',
                'gender' => 'P',
                'phone'  => '081234567802',
            ],
            [
                'name'   => 'Budi Santoso, S.Pd',
                'email'  => 'budi.santoso@sekolah.test',
                'nip'    => '198703032012011001',
                'type'   => 'guru_kelas',
                'gender' => 'L',
                'phone'  => '081234567803',
            ],

            // ── Guru Bidang 4–6 (wali kelas + mengajar mapel spesifik) ─────────
            [
                'name'   => 'Dewi Kartika, S.Pd',
                'email'  => 'dewi.kartika@sekolah.test',
                'nip'    => '198804042013012001',
                'type'   => 'guru_bidang',
                'gender' => 'P',
                'phone'  => '081234567804',
            ],
            [
                'name'   => 'Hendra Gunawan, S.Pd',
                'email'  => 'hendra.gunawan@sekolah.test',
                'nip'    => '198905052014011001',
                'type'   => 'guru_bidang',
                'gender' => 'L',
                'phone'  => '081234567805',
            ],
            [
                'name'   => 'Ratna Dewi, S.Pd',
                'email'  => 'ratna.dewi@sekolah.test',
                'nip'    => '199006062015012001',
                'type'   => 'guru_bidang',
                'gender' => 'P',
                'phone'  => '081234567806',
            ],

            // ── Guru Bidang 4–6 (murni pengajar mapel) ───────────────────────
            [
                'name'   => 'Yusuf Hidayat, S.Pd',
                'email'  => 'yusuf.hidayat@sekolah.test',
                'nip'    => '199107072016011001',
                'type'   => 'guru_bidang',
                'gender' => 'L',
                'phone'  => '081234567807',
            ],
            [
                'name'   => 'Nurul Aini, S.Pd',
                'email'  => 'nurul.aini@sekolah.test',
                'nip'    => '199208082017012001',
                'type'   => 'guru_bidang',
                'gender' => 'P',
                'phone'  => '081234567808',
            ],
            [
                'name'   => 'Agus Setiawan, S.Pd',
                'email'  => 'agus.setiawan@sekolah.test',
                'nip'    => '199309092018011001',
                'type'   => 'guru_bidang',
                'gender' => 'L',
                'phone'  => '081234567809',
            ],
            [
                'name'   => 'Fitri Handayani, S.Pd',
                'email'  => 'fitri.handayani@sekolah.test',
                'nip'    => '199410102019012001',
                'type'   => 'guru_bidang',
                'gender' => 'P',
                'phone'  => '081234567810',
            ],
        ];

        foreach ($teachers as $data) {
            DB::transaction(function () use ($data) {
                $user = User::create([
                    'name'     => $data['name'],
                    'email'    => $data['email'],
                    'password' => Hash::make('password'),
                ]);

                $user->assignRole('guru');

                Teacher::create([
                    'user_id' => $user->id,
                    'nip'     => $data['nip'],
                    'type'    => $data['type'],
                    'gender'  => $data['gender'],
                    'phone'   => $data['phone'],
                ]);
            });
        }
    }
}
