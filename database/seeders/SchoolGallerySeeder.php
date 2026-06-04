<?php

namespace Database\Seeders;

use App\Models\SchoolGallery;
use Illuminate\Database\Seeder;

class SchoolGallerySeeder extends Seeder
{
    public function run(): void
    {
        $videos = [
            [
                'type'       => 'video',
                'video_url'  => 'https://www.youtube.com/watch?v=MzJ0uf7sPeU',
                'caption'    => 'Pentas Seni & Perpisahan Kelas 6 2024',
                'sort_order' => 1,
            ],
            [
                'type'       => 'video',
                'video_url'  => 'https://www.youtube.com/watch?v=qeMFqkcPYcg',
                'caption'    => 'Drumband MI Nurul Ulum — Peringatan HUT RI ke-79',
                'sort_order' => 2,
            ],
            [
                'type'       => 'video',
                'video_url'  => 'https://www.youtube.com/watch?v=BRRolKTlF6Q',
                'caption'    => 'Kegiatan Pramuka — Jambore Tingkat Kecamatan 2024',
                'sort_order' => 3,
            ],
            [
                'type'       => 'video',
                'video_url'  => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'caption'    => 'Wisuda & Pelepasan Santri Tahfidz Angkatan 2024',
                'sort_order' => 4,
            ],
            [
                'type'       => 'video',
                'video_url'  => 'https://www.youtube.com/watch?v=9bZkp7q19f0',
                'caption'    => 'Lomba Pencak Silat — O2SN Tingkat Provinsi 2024',
                'sort_order' => 5,
            ],
            [
                'type'       => 'video',
                'video_url'  => 'https://www.youtube.com/watch?v=kXYiU_JCYtU',
                'caption'    => 'Pentas Seni Tari Tradisional — Festival Budaya 2023',
                'sort_order' => 6,
            ],
            [
                'type'       => 'video',
                'video_url'  => 'https://www.youtube.com/watch?v=JGwWNGJdvx8',
                'caption'    => 'Upacara Peringatan Hari Pahlawan 10 November 2023',
                'sort_order' => 7,
            ],
            [
                'type'       => 'video',
                'video_url'  => 'https://www.youtube.com/watch?v=2Vv-BfVoq4g',
                'caption'    => 'Pesantren Kilat Ramadan 1445 H — Kegiatan Tahfidz & Ibadah',
                'sort_order' => 8,
            ],
        ];

        foreach ($videos as $video) {
            SchoolGallery::firstOrCreate(
                ['video_url' => $video['video_url']],
                $video
            );
        }
    }
}
