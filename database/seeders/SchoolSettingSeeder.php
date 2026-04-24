<?php

namespace Database\Seeders;

use App\Models\SchoolSetting;
use Illuminate\Database\Seeder;

class SchoolSettingSeeder extends Seeder
{
    public function run(): void
    {
        SchoolSetting::updateOrCreate(['id' => 1], [
            'name'           => 'MI Nurul Ulum',
            'tagline'        => 'Berilmu, Berakhlak, Berprestasi',
            'npsn'           => '60714321',
            'principal_name' => 'Drs. H. Mahmud Syafi\'i, M.Pd',
            'principal_nip'  => '196504151990031007',
            'address'        => 'Jl. Pesantren No. 12, Desa Karanganyar, Kec. Paiton, Kab. Probolinggo, Jawa Timur 67291',
            'phone'          => '0335-771234',
            'email'          => 'mi.nurululum@gmail.com',
            'website'        => 'https://mi-nurululum.sch.id',
            'description'    => 'MI Nurul Ulum adalah madrasah ibtidaiyah yang berdiri sejak tahun 1985 di bawah naungan Yayasan Pendidikan Islam Nurul Ulum. Kami berkomitmen menghadirkan pendidikan berkualitas yang memadukan ilmu pengetahuan umum dengan nilai-nilai keislaman yang kuat.',
            'vision'         => 'Terwujudnya peserta didik yang cerdas, berakhlak mulia, dan berprestasi dalam bidang akademik maupun non-akademik berlandaskan iman dan taqwa kepada Allah SWT.',
            'mission'        => "Menyelenggarakan pembelajaran yang aktif, kreatif, efektif, dan menyenangkan.\nMengintegrasikan nilai-nilai keislaman dalam setiap aspek pembelajaran.\nMengembangkan potensi peserta didik secara optimal melalui kegiatan akademik dan ekstrakulikuler.\nMembangun karakter peserta didik yang jujur, disiplin, tanggung jawab, dan peduli lingkungan.\nMenjalin kerjasama yang harmonis antara madrasah, orang tua, dan masyarakat.",
            'history'        => 'MI Nurul Ulum didirikan pada tahun 1985 oleh KH. Abdul Halim bersama tokoh masyarakat setempat dengan visi memajukan pendidikan Islam di wilayah Paiton. Berawal dari sebuah mushola kecil dengan 40 santri, madrasah ini terus berkembang pesat. Pada tahun 2000, gedung permanen tiga lantai berhasil dibangun berkat dukungan warga dan donatur. Kini MI Nurul Ulum telah meluluskan lebih dari 2.000 alumni yang tersebar di berbagai penjuru nusantara dan menjadi kebanggaan masyarakat Karanganyar.',
        ]);
    }
}
