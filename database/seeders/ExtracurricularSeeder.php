<?php

namespace Database\Seeders;

use App\Models\Extracurricular;
use Illuminate\Database\Seeder;

class ExtracurricularSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'name'        => 'Pramuka',
                'description' => 'Membentuk karakter mandiri, disiplin, dan jiwa kepemimpinan melalui kegiatan kepanduan yang menyenangkan.',
                'sort_order'  => 1,
            ],
            [
                'name'        => 'Tahfidz Al-Qur\'an',
                'description' => 'Program hafalan Al-Qur\'an dengan metode talaqqi yang dibimbing langsung oleh ustadz berpengalaman.',
                'sort_order'  => 2,
            ],
            [
                'name'        => 'Kaligrafi',
                'description' => 'Seni menulis indah Arab yang mengasah kreativitas dan kecintaan terhadap budaya Islam.',
                'sort_order'  => 3,
            ],
            [
                'name'        => 'Futsal',
                'description' => 'Olahraga tim yang melatih kerjasama, sportivitas, dan kebugaran jasmani siswa.',
                'sort_order'  => 4,
            ],
            [
                'name'        => 'Drumband',
                'description' => 'Kegiatan marching band yang melatih kedisiplinan, kekompakan, dan apresiasi seni musik.',
                'sort_order'  => 5,
            ],
            [
                'name'        => 'Seni Tari',
                'description' => 'Melestarikan budaya daerah melalui latihan tari tradisional dan kontemporer.',
                'sort_order'  => 6,
            ],
            [
                'name'        => 'Pencak Silat',
                'description' => 'Bela diri tradisional Nusantara yang mengajarkan ketangkasan, kedisiplinan, dan nilai kesatria.',
                'sort_order'  => 7,
            ],
            [
                'name'        => 'Palang Merah Remaja',
                'description' => 'Melatih kepedulian sosial, pertolongan pertama, dan jiwa kemanusiaan sejak dini.',
                'sort_order'  => 8,
            ],
        ];

        foreach ($data as $item) {
            Extracurricular::firstOrCreate(
                ['name' => $item['name']],
                array_merge($item, ['is_active' => true])
            );
        }
    }
}
