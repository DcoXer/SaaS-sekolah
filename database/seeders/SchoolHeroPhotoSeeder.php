<?php

namespace Database\Seeders;

use App\Models\SchoolHeroPhoto;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class SchoolHeroPhotoSeeder extends Seeder
{
    // Warna tema per halaman [R, G, B]
    private array $pageColors = [
        'welcome' => [
            [[34, 85, 50],   [21, 128, 61]],   // hijau tua → hijau
            [[20, 83, 45],   [6,  95, 70]],    // hijau tua → teal
            [[15, 118, 110], [13, 148, 136]],  // teal gelap → teal
        ],
        'ekskul' => [
            [[30, 64, 175],  [37, 99, 235]],   // biru tua → biru
            [[76, 29, 149],  [109, 40, 217]],  // ungu tua → ungu
            [[21, 128, 61],  [34, 197, 94]],   // hijau tua → hijau
        ],
        'galeri' => [
            [[124, 45, 18],  [194, 65, 12]],   // oranye tua → oranye
            [[113, 63, 18],  [161, 98, 7]],    // amber tua → amber
            [[120, 53, 15],  [180, 83, 9]],    // cokelat → oranye
        ],
        'tentang' => [
            [[30, 58, 138],  [29, 78, 216]],   // biru navy → biru
            [[15, 118, 110], [20, 184, 166]],  // teal → teal muda
            [[23, 37, 84],   [30, 64, 175]],   // navy tua → biru
        ],
    ];

    public function run(): void
    {
        $disk = Storage::disk('public');
        $disk->makeDirectory('hero');

        foreach ($this->pageColors as $page => $colorPairs) {
            foreach ($colorPairs as $order => $colors) {
                $filename  = "hero/{$page}-{$order}.jpg";

                // Skip jika sudah ada
                if (SchoolHeroPhoto::where('page', $page)->where('order', $order + 1)->exists()) {
                    continue;
                }

                // Buat gambar gradient 1280×480
                $img = $this->makeGradient(1280, 480, $colors[0], $colors[1]);

                $disk->put($filename, $img);

                SchoolHeroPhoto::create([
                    'page'      => $page,
                    'file_path' => $filename,
                    'order'     => $order + 1,
                ]);
            }
        }
    }

    /**
     * Generate JPEG gradient image, return raw bytes string.
     */
    private function makeGradient(int $w, int $h, array $from, array $to): string
    {
        $img = imagecreatetruecolor($w, $h);

        for ($x = 0; $x < $w; $x++) {
            $ratio = $x / ($w - 1);
            $r     = (int) ($from[0] + ($to[0] - $from[0]) * $ratio);
            $g     = (int) ($from[1] + ($to[1] - $from[1]) * $ratio);
            $b     = (int) ($from[2] + ($to[2] - $from[2]) * $ratio);
            $color = imagecolorallocate($img, $r, $g, $b);
            imageline($img, $x, 0, $x, $h - 1, $color);
        }

        // Tambah titik-titik cahaya supaya tidak terlihat flat
        for ($i = 0; $i < 6; $i++) {
            $cx    = rand((int) ($w * 0.1), (int) ($w * 0.9));
            $cy    = rand((int) ($h * 0.1), (int) ($h * 0.9));
            $light = imagecolorallocatealpha($img, 255, 255, 255, 110);
            imagefilledellipse($img, $cx, $cy, rand(80, 200), rand(80, 200), $light);
        }

        ob_start();
        imagejpeg($img, null, 85);
        $bytes = ob_get_clean();
        imagedestroy($img);

        return $bytes;
    }
}
