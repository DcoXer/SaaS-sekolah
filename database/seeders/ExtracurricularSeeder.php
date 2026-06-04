<?php

namespace Database\Seeders;

use App\Models\Extracurricular;
use App\Models\ExtracurricularAchievement;
use Illuminate\Database\Seeder;

class ExtracurricularSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'name'        => 'Pramuka',
                'description' => 'Membentuk karakter mandiri, disiplin, dan jiwa kepemimpinan melalui kegiatan kepanduan yang menyenangkan. Siswa dilatih keterampilan bertahan hidup, kerja tim, dan cinta alam.',
                'coach'       => 'Bpk. Ahmad Fauzi, S.Pd.',
                'schedule'    => 'Setiap Jumat, 14.00–16.00 WIB',
                'sort_order'  => 1,
                'achievements' => [
                    ['title' => 'Jambore Nasional MI se-Indonesia', 'year' => 2024, 'level' => 'nasional',   'rank' => 'Peserta Terbaik'],
                    ['title' => 'Lomba Pramuka Siaga',              'year' => 2024, 'level' => 'kabupaten', 'rank' => 'Juara 1'],
                    ['title' => 'Lomba Pioneering',                  'year' => 2023, 'level' => 'provinsi',  'rank' => 'Juara 2'],
                    ['title' => 'Kemah Bakti Pramuka',               'year' => 2023, 'level' => 'kabupaten', 'rank' => 'Juara 3'],
                ],
            ],
            [
                'name'        => 'Tahfidz Al-Qur\'an',
                'description' => 'Program hafalan Al-Qur\'an dengan metode talaqqi yang dibimbing langsung oleh ustadz berpengalaman. Siswa ditargetkan mampu menghafal minimal 3 juz selama di MI.',
                'coach'       => 'Ust. Muhammad Yusuf, S.Ag.',
                'schedule'    => 'Setiap Senin & Rabu, 13.30–15.00 WIB',
                'sort_order'  => 2,
                'achievements' => [
                    ['title' => 'Musabaqoh Hifdzil Qur\'an Nasional', 'year' => 2024, 'level' => 'nasional',      'rank' => 'Juara 2'],
                    ['title' => 'MHQ Tingkat Provinsi Jawa Timur',    'year' => 2024, 'level' => 'provinsi',      'rank' => 'Juara 1'],
                    ['title' => 'MHQ Kabupaten 5 Juz Putra',          'year' => 2023, 'level' => 'kabupaten',    'rank' => 'Juara 1'],
                    ['title' => 'MHQ Kabupaten 5 Juz Putri',          'year' => 2023, 'level' => 'kabupaten',    'rank' => 'Juara 1'],
                    ['title' => 'Festival Anak Sholeh',                'year' => 2022, 'level' => 'kecamatan',   'rank' => 'Juara 1'],
                ],
            ],
            [
                'name'        => 'Kaligrafi',
                'description' => 'Seni menulis indah Arab yang mengasah kreativitas dan kecintaan terhadap budaya Islam. Siswa belajar berbagai aliran kaligrafi seperti Naskhi, Tsuluts, dan Diwani.',
                'coach'       => 'Ibu Siti Aminah, S.Pd.I.',
                'schedule'    => 'Setiap Selasa, 13.30–15.30 WIB',
                'sort_order'  => 3,
                'achievements' => [
                    ['title' => 'Lomba Kaligrafi MTQ Nasional Anak', 'year' => 2024, 'level' => 'nasional',   'rank' => 'Juara 3'],
                    ['title' => 'MTQ Tingkat Provinsi Cabang Khat',  'year' => 2023, 'level' => 'provinsi',  'rank' => 'Juara 1'],
                    ['title' => 'Lomba Kaligrafi Hias Kabupaten',    'year' => 2023, 'level' => 'kabupaten', 'rank' => 'Juara 2'],
                    ['title' => 'Festival Seni Islam Kecamatan',     'year' => 2022, 'level' => 'kecamatan', 'rank' => 'Juara 1'],
                ],
            ],
            [
                'name'        => 'Futsal',
                'description' => 'Olahraga tim yang melatih kerjasama, sportivitas, dan kebugaran jasmani siswa. Latihan rutin dilakukan di lapangan futsal sekolah dengan pendampingan pelatih berpengalaman.',
                'coach'       => 'Bpk. Rizky Pratama, S.Or.',
                'schedule'    => 'Setiap Sabtu, 07.00–09.00 WIB',
                'sort_order'  => 4,
                'achievements' => [
                    ['title' => 'Turnamen Futsal Mini SD/MI Kabupaten', 'year' => 2024, 'level' => 'kabupaten', 'rank' => 'Juara 1'],
                    ['title' => 'O2SN Futsal Tingkat Kecamatan',        'year' => 2024, 'level' => 'kecamatan', 'rank' => 'Juara 1'],
                    ['title' => 'Turnamen Futsal Antar SD/MI',          'year' => 2023, 'level' => 'kabupaten', 'rank' => 'Runner Up'],
                    ['title' => 'Turnamen Futsal Ramadan',              'year' => 2023, 'level' => 'kecamatan', 'rank' => 'Juara 1'],
                ],
            ],
            [
                'name'        => 'Drumband',
                'description' => 'Kegiatan marching band yang melatih kedisiplinan, kekompakan, dan apresiasi seni musik. Drumband sekolah aktif tampil dalam berbagai acara kenegaraan dan festival seni.',
                'coach'       => 'Bpk. Hendra Wijaya',
                'schedule'    => 'Setiap Kamis & Sabtu, 14.00–16.00 WIB',
                'sort_order'  => 5,
                'achievements' => [
                    ['title' => 'Festival Drumband Pelajar Provinsi', 'year' => 2024, 'level' => 'provinsi',  'rank' => 'Juara 2'],
                    ['title' => 'Lomba Drumband HUT RI Kabupaten',    'year' => 2023, 'level' => 'kabupaten', 'rank' => 'Juara 1'],
                    ['title' => 'Parade Drumband Kecamatan',          'year' => 2023, 'level' => 'kecamatan', 'rank' => 'Penampil Terbaik'],
                ],
            ],
            [
                'name'        => 'Seni Tari',
                'description' => 'Melestarikan budaya daerah melalui latihan tari tradisional dan kontemporer. Siswa diajarkan berbagai tarian khas Nusantara yang memperkaya wawasan budaya bangsa.',
                'coach'       => 'Ibu Dewi Kusuma, S.Sn.',
                'schedule'    => 'Setiap Rabu, 14.00–16.00 WIB',
                'sort_order'  => 6,
                'achievements' => [
                    ['title' => 'Festival Tari Tradisional Jawa Timur',   'year' => 2024, 'level' => 'provinsi',  'rank' => 'Juara 1'],
                    ['title' => 'Lomba Tari Kreasi Anak Kabupaten',       'year' => 2023, 'level' => 'kabupaten', 'rank' => 'Juara 2'],
                    ['title' => 'Pentas Seni Budaya Tingkat Kecamatan',   'year' => 2022, 'level' => 'kecamatan', 'rank' => 'Penampil Terbaik'],
                ],
            ],
            [
                'name'        => 'Pencak Silat',
                'description' => 'Bela diri tradisional Nusantara yang mengajarkan ketangkasan, kedisiplinan, dan nilai kesatria. Siswa dilatih teknik dasar, jurus, dan tanding dalam suasana yang aman dan menyenangkan.',
                'coach'       => 'Bpk. Agus Santoso, S.Pd.',
                'schedule'    => 'Setiap Selasa & Kamis, 15.00–17.00 WIB',
                'sort_order'  => 7,
                'achievements' => [
                    ['title' => 'Kejuaraan Pencak Silat Pelajar Nasional', 'year' => 2024, 'level' => 'nasional',   'rank' => 'Juara 3'],
                    ['title' => 'O2SN Pencak Silat Provinsi Jawa Timur',   'year' => 2024, 'level' => 'provinsi',  'rank' => 'Juara 1'],
                    ['title' => 'Kejuaraan Silat Pelajar Kabupaten',       'year' => 2023, 'level' => 'kabupaten', 'rank' => 'Juara 1'],
                    ['title' => 'Open Tournament Pencak Silat',            'year' => 2023, 'level' => 'kabupaten', 'rank' => 'Juara 2'],
                    ['title' => 'Turnamen Silat Antar SD/MI Kecamatan',   'year' => 2022, 'level' => 'kecamatan', 'rank' => 'Juara 1'],
                ],
            ],
            [
                'name'        => 'Palang Merah Remaja',
                'description' => 'Melatih kepedulian sosial, pertolongan pertama, dan jiwa kemanusiaan sejak dini. Anggota PMR aktif terlibat dalam kegiatan donor darah, penyuluhan kesehatan, dan bakti sosial.',
                'coach'       => 'Ibu Rahma Indriani, S.Kep.',
                'schedule'    => 'Setiap Sabtu, 09.00–11.00 WIB',
                'sort_order'  => 8,
                'achievements' => [
                    ['title' => 'Lomba PMR Madya Tingkat Provinsi',  'year' => 2024, 'level' => 'provinsi',  'rank' => 'Juara 2'],
                    ['title' => 'Lomba UKS & PMR Kabupaten',         'year' => 2023, 'level' => 'kabupaten', 'rank' => 'Juara 1'],
                    ['title' => 'Jumbara PMR Tingkat Kecamatan',     'year' => 2022, 'level' => 'kecamatan', 'rank' => 'Juara 1'],
                ],
            ],
        ];

        foreach ($data as $item) {
            $achievements = $item['achievements'] ?? [];
            unset($item['achievements']);

            $ekskul = Extracurricular::firstOrCreate(
                ['name' => $item['name']],
                array_merge($item, ['is_active' => true])
            );

            // Update coach & schedule jika ekskul sudah ada (dari seeder lama)
            $ekskul->update([
                'coach'       => $item['coach'] ?? $ekskul->coach,
                'schedule'    => $item['schedule'] ?? $ekskul->schedule,
                'description' => $item['description'] ?? $ekskul->description,
            ]);

            // Seed achievements (skip jika sudah ada)
            foreach ($achievements as $i => $achievement) {
                ExtracurricularAchievement::firstOrCreate(
                    [
                        'extracurricular_id' => $ekskul->id,
                        'title'              => $achievement['title'],
                        'year'               => $achievement['year'],
                    ],
                    array_merge($achievement, [
                        'extracurricular_id' => $ekskul->id,
                        'sort_order'         => $i,
                    ])
                );
            }
        }
    }
}
