<?php

namespace App\Helpers;

use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Encoder\Encoder;

class QrCodeHelper
{
    /**
     * Generate QR code as base64 PNG using GD (no Imagick required).
     */
    public static function pngBase64(string $data, int $moduleSize = 4, int $margin = 2): string
    {
        $qr     = Encoder::encode($data, ErrorCorrectionLevel::M());
        $matrix = $qr->getMatrix();
        $count  = $matrix->getWidth();

        $dim = ($count + $margin * 2) * $moduleSize;
        $img = imagecreatetruecolor($dim, $dim);

        $white = imagecolorallocate($img, 255, 255, 255);
        $black = imagecolorallocate($img, 0,   0,   0);

        imagefill($img, 0, 0, $white);

        for ($y = 0; $y < $count; $y++) {
            for ($x = 0; $x < $count; $x++) {
                if ($matrix->get($x, $y) === 1) {
                    $px = ($margin + $x) * $moduleSize;
                    $py = ($margin + $y) * $moduleSize;
                    imagefilledrectangle($img, $px, $py, $px + $moduleSize - 1, $py + $moduleSize - 1, $black);
                }
            }
        }

        ob_start();
        imagepng($img);
        $png = ob_get_clean();
        imagedestroy($img);

        return base64_encode($png);
    }
}
