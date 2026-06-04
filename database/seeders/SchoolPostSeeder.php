<?php

namespace Database\Seeders;

use App\Models\SchoolPost;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SchoolPostSeeder extends Seeder
{
    public function run(): void
    {
        $posts = [
            [
                'title'        => 'Selamat! Siswa MI Nurul Ulum Raih Juara 1 MHQ Tingkat Provinsi',
                'category'     => 'berita',
                'excerpt'      => 'Kebanggaan bagi seluruh keluarga besar MI Nurul Ulum! Dua siswa terbaik kita berhasil meraih juara pertama pada Musabaqoh Hifdzil Qur\'an tingkat Provinsi Jawa Timur.',
                'content'      => '<p>Alhamdulillah, kebanggaan besar bagi seluruh keluarga besar MI Nurul Ulum. Dua siswa terbaik kita, <strong>Ahmad Zaki Mubarak</strong> (kelas 5) dan <strong>Fatimah Az-Zahra</strong> (kelas 6), berhasil meraih <strong>Juara 1</strong> pada Musabaqoh Hifdzil Qur\'an (MHQ) tingkat Provinsi Jawa Timur yang diselenggarakan di Surabaya.</p><p>Kompetisi ini diikuti oleh ratusan peserta dari berbagai kabupaten/kota se-Jawa Timur. Ahmad Zaki tampil memukau dengan hafalan 10 juz tanpa satu pun kesalahan, sementara Fatimah Az-Zahra memukau dewan juri dengan kelancaran hafalan 15 juz beserta tajwid yang sempurna.</p><p>"Alhamdulillah, ini adalah buah dari latihan panjang dan doa orang tua. Semoga menjadi motivasi adik-adik kelas," ujar Ahmad Zaki dengan rendah hati.</p><p>Kepala Madrasah menyampaikan rasa syukur dan terima kasih kepada para guru pembimbing, orang tua siswa, serta seluruh civitas akademika yang telah mendukung program Tahfidz Al-Qur\'an di sekolah.</p>',
                'published_at' => now()->subDays(2),
                'is_published' => true,
            ],
            [
                'title'        => 'Penerimaan Peserta Didik Baru (PPDB) Tahun Ajaran 2025/2026 Resmi Dibuka',
                'category'     => 'pengumuman',
                'excerpt'      => 'MI Nurul Ulum dengan bangga mengumumkan pembukaan Penerimaan Peserta Didik Baru (PPDB) untuk tahun ajaran 2025/2026. Daftarkan putra-putri Anda sekarang!',
                'content'      => '<p>MI Nurul Ulum dengan bangga mengumumkan pembukaan <strong>Penerimaan Peserta Didik Baru (PPDB)</strong> untuk Tahun Ajaran 2025/2026. Kami membuka pendaftaran bagi putra-putri Anda yang ingin mendapatkan pendidikan berkualitas berlandaskan nilai-nilai Islam.</p><h3>Persyaratan Pendaftaran</h3><ul><li>Berusia minimal 6 tahun pada 1 Juli 2025</li><li>Memiliki ijazah/STTB TK/RA atau sederajat</li><li>Foto copy akte kelahiran (3 lembar)</li><li>Foto copy KK (3 lembar)</li><li>Pas foto 3x4 (4 lembar)</li></ul><h3>Jadwal PPDB</h3><ul><li><strong>Pendaftaran Online:</strong> 1 April – 31 Mei 2025</li><li><strong>Seleksi & Tes:</strong> 5–7 Juni 2025</li><li><strong>Pengumuman Hasil:</strong> 10 Juni 2025</li><li><strong>Daftar Ulang:</strong> 11–20 Juni 2025</li></ul><p>Pendaftaran dapat dilakukan secara online melalui website sekolah atau datang langsung ke sekretariat MI Nurul Ulum. Kuota terbatas, segera daftarkan!</p>',
                'published_at' => now()->subDays(7),
                'is_published' => true,
            ],
            [
                'title'        => 'Tim Futsal MI Nurul Ulum Juara 1 Turnamen Futsal Mini Kabupaten',
                'category'     => 'berita',
                'excerpt'      => 'Mengharumkan nama sekolah di pentas olahraga! Tim futsal putra MI Nurul Ulum berhasil meraih juara pertama dalam Turnamen Futsal Mini SD/MI se-Kabupaten.',
                'content'      => '<p>Semangat juang dan kerja keras akhirnya membuahkan hasil! Tim Futsal Putra MI Nurul Ulum berhasil meraih <strong>Juara 1</strong> dalam Turnamen Futsal Mini SD/MI se-Kabupaten yang digelar selama tiga hari di GOR Kabupaten.</p><p>Tim yang dilatih oleh Bpk. Rizky Pratama ini mengalahkan 24 tim peserta dari berbagai sekolah di seluruh kabupaten. Di babak final, tim kita berhadapan dengan SD Negeri 1 dan menang dengan skor telak 4–1.</p><p>Kapten tim, <strong>Rafif Ardian</strong> (kelas 6), dinobatkan sebagai <em>Top Scorer</em> dengan torehan 8 gol sepanjang turnamen. Sementara kiper <strong>Daffa Rizqullah</strong> meraih penghargaan <em>Best Goalkeeper</em>.</p><p>Pelatih Bpk. Rizky menyampaikan, "Anak-anak berlatih sangat keras selama 3 bulan terakhir. Kemenangan ini adalah milik mereka, orang tua, dan seluruh warga sekolah."</p>',
                'published_at' => now()->subDays(10),
                'is_published' => true,
            ],
            [
                'title'        => 'Kegiatan Pesantren Kilat Ramadan 1446 H: Mempererat Ukhuwah dan Meningkatkan Iman',
                'category'     => 'berita',
                'excerpt'      => 'Selama tiga hari penuh, siswa kelas 4–6 mengikuti pesantren kilat Ramadan yang dipenuhi dengan kegiatan ibadah, pengajian, dan kreativitas Islami.',
                'content'      => '<p>Dalam rangka mengisi bulan suci Ramadan 1446 H dengan kegiatan yang bermakna, MI Nurul Ulum menyelenggarakan <strong>Pesantren Kilat Ramadan</strong> selama tiga hari untuk siswa kelas 4 hingga 6.</p><p>Kegiatan yang dipadati berbagai program menarik ini meliputi:</p><ul><li><strong>Sholat berjamaah</strong> Dhuhur dan Ashar</li><li><strong>Tadarus Al-Qur\'an</strong> dan setoran hafalan</li><li><strong>Kajian Islam</strong> oleh ustadz tamu</li><li><strong>Lomba-lomba Islami</strong>: adzan, hafalan surat pendek, dan kaligrafi</li><li><strong>Buka bersama</strong> dan sahur bersama (malam terakhir)</li></ul><p>Kepala Madrasah menyampaikan harapannya agar kegiatan ini menjadi momentum bagi siswa untuk semakin mencintai Al-Qur\'an dan meningkatkan kualitas ibadah sehari-hari. "Ramadan adalah sekolah terbaik. Semoga anak-anak kita lulus dengan nilai terbaik," tuturnya.</p>',
                'published_at' => now()->subDays(20),
                'is_published' => true,
            ],
            [
                'title'        => 'Pengumuman Jadwal Ujian Akhir Semester Genap Tahun Ajaran 2024/2025',
                'category'     => 'pengumuman',
                'excerpt'      => 'Berikut informasi resmi mengenai jadwal pelaksanaan Ujian Akhir Semester (UAS) Genap untuk seluruh kelas. Mohon perhatikan tanggal dan ketentuan yang berlaku.',
                'content'      => '<p>Diberitahukan kepada seluruh siswa dan orang tua/wali murid MI Nurul Ulum bahwa <strong>Ujian Akhir Semester (UAS) Genap</strong> Tahun Ajaran 2024/2025 akan dilaksanakan sesuai jadwal berikut:</p><h3>Jadwal Pelaksanaan</h3><ul><li><strong>Kelas 1–2:</strong> 9–13 Juni 2025</li><li><strong>Kelas 3–5:</strong> 9–14 Juni 2025</li><li><strong>Kelas 6:</strong> Ujian Madrasah telah dilaksanakan April 2025</li></ul><h3>Ketentuan Ujian</h3><ul><li>Siswa wajib hadir 15 menit sebelum ujian dimulai</li><li>Berseragam lengkap dan rapi sesuai ketentuan</li><li>Membawa alat tulis sendiri</li><li>Pastikan tagihan SPP tidak ada tunggakan</li></ul><p>Informasi lebih lanjut dapat menghubungi wali kelas masing-masing atau sekretariat sekolah. Semangat belajar!</p>',
                'published_at' => now()->subDays(35),
                'is_published' => true,
            ],
            [
                'title'        => 'Peringatan Hari Pendidikan Nasional: Aksi Sosial dan Pentas Seni Siswa',
                'category'     => 'berita',
                'excerpt'      => 'Memperingati Hari Pendidikan Nasional 2 Mei, MI Nurul Ulum menggelar serangkaian kegiatan bermakna mulai dari aksi sosial berbagi sembako hingga pentas seni yang memukau.',
                'content'      => '<p>Dalam rangka memperingati <strong>Hari Pendidikan Nasional (Hardiknas) 2 Mei 2025</strong>, MI Nurul Ulum menyelenggarakan serangkaian kegiatan yang sarat makna dan inspirasi.</p><p>Pagi hari dimulai dengan <strong>Upacara Bendera</strong> yang khidmat, dilanjutkan dengan sambutan Kepala Madrasah tentang pentingnya pendidikan karakter di era modern. "Anak yang cerdas tanpa akhlak adalah bencana. Anak yang berakhlak tanpa ilmu adalah kelemahan. Kita butuh keduanya," tegasnya.</p><p>Siang harinya, ratusan siswa bersama guru menggelar <strong>Aksi Sosial Berbagi Sembako</strong> kepada warga kurang mampu di sekitar sekolah. Kegiatan ini menjadi pelajaran nyata tentang kepedulian sosial.</p><p>Sore hari ditutup dengan <strong>Pentas Seni</strong> yang menampilkan tari tradisional, pembacaan puisi, pertunjukan drumband, dan pameran karya kaligrafi siswa. Acara ini dihadiri ratusan orang tua dan wali murid.</p>',
                'published_at' => now()->subDays(50),
                'is_published' => true,
            ],
            [
                'title'        => 'Kunjungan Edukatif ke Perpustakaan Daerah: Menumbuhkan Minat Baca Sejak Dini',
                'category'     => 'berita',
                'excerpt'      => 'Siswa kelas 3 dan 4 mengikuti kunjungan edukatif ke Perpustakaan Daerah untuk menumbuhkan kecintaan membaca dan memperluas wawasan di luar kelas.',
                'content'      => '<p>Sebanyak 120 siswa kelas 3 dan 4 MI Nurul Ulum mengikuti <strong>Kunjungan Edukatif</strong> ke Perpustakaan Daerah Kabupaten. Kegiatan ini merupakan bagian dari program literasi sekolah yang bertujuan menumbuhkan minat baca sejak usia dini.</p><p>Di perpustakaan, siswa disambut oleh petugas dan diajak berkeliling untuk mengenal berbagai koleksi buku. Mereka diperkenalkan dengan cara menggunakan katalog, meminjam buku, dan pentingnya menjaga koleksi perpustakaan.</p><p>Sesi yang paling disukai adalah <strong>Story Telling</strong> oleh pustakawan, di mana cerita-cerita rakyat Indonesia dibawakan dengan cara yang interaktif dan menyenangkan. Tawa dan antusias siswa memenuhi ruangan.</p><p>Guru pendamping, Ibu Nur Laila, menyampaikan bahwa kegiatan seperti ini sangat penting untuk membiasakan anak-anak berinteraksi langsung dengan buku fisik di era digital.</p>',
                'published_at' => now()->subDays(65),
                'is_published' => true,
            ],
            [
                'title'        => 'MI Nurul Ulum Raih Akreditasi A: Komitmen Terhadap Kualitas Pendidikan',
                'category'     => 'berita',
                'excerpt'      => 'Bangga! MI Nurul Ulum resmi meraih peringkat Akreditasi A dari Badan Akreditasi Nasional Sekolah/Madrasah (BAN-S/M). Ini adalah bukti komitmen kami dalam memberikan pendidikan terbaik.',
                'content'      => '<p>Alhamdulillah, kabar membanggakan kembali datang dari MI Nurul Ulum. Madrasah kita resmi meraih <strong>Akreditasi A</strong> dari Badan Akreditasi Nasional Sekolah/Madrasah (BAN-S/M) dengan nilai 92 dari 100.</p><p>Penilaian akreditasi mencakup delapan standar nasional pendidikan, yaitu standar isi, proses, kompetensi lulusan, pendidik dan tenaga kependidikan, sarana dan prasarana, pengelolaan, pembiayaan, dan penilaian pendidikan.</p><p>"Akreditasi A ini bukan tujuan akhir, melainkan awal dari kerja keras yang lebih besar. Kami berkomitmen untuk terus meningkatkan kualitas layanan pendidikan bagi seluruh siswa," ujar Kepala Madrasah.</p><p>Ucapan terima kasih disampaikan kepada seluruh guru, staf, orang tua siswa, dan masyarakat yang telah bersama-sama mendukung kemajuan madrasah tercinta ini.</p>',
                'published_at' => now()->subDays(90),
                'is_published' => true,
            ],
        ];

        foreach ($posts as $post) {
            SchoolPost::firstOrCreate(
                ['slug' => Str::slug($post['title'])],
                array_merge($post, ['slug' => Str::slug($post['title'])])
            );
        }
    }
}
