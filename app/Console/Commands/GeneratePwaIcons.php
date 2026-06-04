<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('pwa:icons')]
#[Description('Generate PWA PNG icons ke public/icons/')]
class GeneratePwaIcons extends Command
{
    public function handle(): void
    {
        $sizes = [72, 96, 128, 144, 152, 192, 384, 512];

        foreach ($sizes as $size) {
            $this->makeIcon($size);
        }

        // Apple touch icon
        $this->makeIcon(180, public_path('icons/apple-touch-icon.png'));

        $this->info('PWA icons generated di public/icons/');
    }

    private function makeIcon(int $size, ?string $dest = null): void
    {
        $dest ??= public_path("icons/icon-{$size}x{$size}.png");

        $img   = imagecreatetruecolor($size, $size);
        $green = imagecolorallocate($img, 16, 185, 129);   // emerald-500 #10b981
        $white = imagecolorallocate($img, 255, 255, 255);
        $dark  = imagecolorallocate($img, 5, 150, 105);    // emerald-600

        // Background rounded rect via filled circle corners
        imagefill($img, 0, 0, $green);
        $r = (int) ($size * 0.18);
        $this->roundedRect($img, 0, 0, $size - 1, $size - 1, $r, $green);

        // Building body
        $pad = (int) ($size * 0.18);
        $bx1 = $pad;
        $bx2 = $size - $pad;
        $by2 = $size - $pad;
        $by1 = (int) ($size * 0.30);
        imagefilledrectangle($img, $bx1, $by1, $bx2, $by2, $white);

        // Roof (triangle via polygon)
        $mx  = (int) ($size / 2);
        $rx1 = $bx1 - (int) ($size * 0.04);
        $rx2 = $bx2 + (int) ($size * 0.04);
        $ry  = (int) ($size * 0.18);
        imagefilledpolygon($img, [$rx1, $by1, $rx2, $by1, $mx, $ry], $white);

        // Door
        $dw = (int) ($size * 0.14);
        $dh = (int) ($size * 0.16);
        $dx = $mx - (int) ($dw / 2);
        $dy = $by2 - $dh;
        imagefilledrectangle($img, $dx, $dy, $dx + $dw, $by2, $green);

        // Windows
        $ww = (int) ($size * 0.10);
        $wh = (int) ($size * 0.10);
        $wy = $by1 + (int) ($size * 0.12);
        imagefilledrectangle($img, $bx1 + (int) ($size * 0.08), $wy, $bx1 + (int) ($size * 0.08) + $ww, $wy + $wh, $green);
        imagefilledrectangle($img, $bx2 - (int) ($size * 0.08) - $ww, $wy, $bx2 - (int) ($size * 0.08), $wy + $wh, $green);

        imagepng($img, $dest);
        imagedestroy($img);

        $this->line("  ✓ " . basename($dest));
    }

    private function roundedRect($img, int $x1, int $y1, int $x2, int $y2, int $r, $color): void
    {
        imagefilledrectangle($img, $x1 + $r, $y1, $x2 - $r, $y2, $color);
        imagefilledrectangle($img, $x1, $y1 + $r, $x2, $y2 - $r, $color);
        imagefilledellipse($img, $x1 + $r, $y1 + $r, $r * 2, $r * 2, $color);
        imagefilledellipse($img, $x2 - $r, $y1 + $r, $r * 2, $r * 2, $color);
        imagefilledellipse($img, $x1 + $r, $y2 - $r, $r * 2, $r * 2, $color);
        imagefilledellipse($img, $x2 - $r, $y2 - $r, $r * 2, $r * 2, $color);
    }
}
