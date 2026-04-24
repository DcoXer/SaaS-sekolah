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
                'type'        => 'video',
                'video_url'   => 'https://www.youtube.com/watch?v=MzJ0uf7sPeU',
                'caption'     => 'Pentas Seni & Perpisahan Kelas 6 2024',
                'sort_order'  => 1,
            ],
            [
                'type'        => 'video',
                'video_url'   => 'https://www.youtube.com/watch?v=qeMFqkcPYcg',
                'caption'     => 'Drumband MI Nurul Ulum — Peringatan HUT RI ke-79',
                'sort_order'  => 2,
            ],
            [
                'type'        => 'video',
                'video_url'   => 'https://www.youtube.com/watch?v=BRRolKTlF6Q',
                'caption'     => 'Kegiatan Pramuka — Jambore Tingkat Kecamatan 2024',
                'sort_order'  => 3,
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
