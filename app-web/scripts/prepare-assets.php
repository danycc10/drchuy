<?php

$source = dirname(__DIR__, 2).'/Medicina_Respiratoria_Web/02_LOGOS_Y_FOTOGRAFIAS_ORIGINALES';
$target = dirname(__DIR__).'/public/images';
if (! is_dir($target)) {
    mkdir($target, 0755, true);
}
$photos = [
    'doctor' => 'FOTO_01_RETRATO_MEDICO_ORIGINAL.jpg',
    'consultorio' => 'FOTO_02_INTERIOR_CONSULTORIO_ORIGINAL.jpg',
    'fachada' => 'FOTO_03_EXTERIOR_ESTABLECIMIENTO_ORIGINAL.jpg',
    'entrada' => 'FOTO_04_ENTRADA_CONSULTORIO_3_ORIGINAL.jpg',
];
foreach ($photos as $name => $file) {
    $image = imagecreatefromjpeg($source.'/'.$file);
    $width = min(imagesx($image), 1200);
    $derived = imagescale($image, $width);
    imagewebp($derived, $target.'/'.$name.'.webp', 85);
    imagedestroy($derived);
    imagedestroy($image);
}
// Only transparent margins are removed. The original brand drawing is not changed.
foreach (['logo' => 'LOGO_OFICIAL_AZUL_GRIS_ORIGINAL.png', 'logo-white' => 'LOGO_OFICIAL_BLANCO_ORIGINAL.png'] as $name => $file) {
    $logo = imagecreatefrompng($source.'/'.$file);
    // PNGs use several RGB values for fully transparent pixels; crop by alpha only.
    $left = imagesx($logo);
    $top = imagesy($logo);
    $right = $bottom = -1;
    for ($y = 0; $y < imagesy($logo); $y++) {
        for ($x = 0; $x < imagesx($logo); $x++) {
            if (((imagecolorat($logo, $x, $y) >> 24) & 0x7f) < 127) {
                $left = min($left, $x);
                $right = max($right, $x);
                $top = min($top, $y);
                $bottom = max($bottom, $y);
            }
        }
    }
    $cropped = imagecrop($logo, ['x' => $left, 'y' => $top, 'width' => $right - $left + 1, 'height' => $bottom - $top + 1]);
    imagesavealpha($cropped, true);
    imagepng($cropped, $target.'/'.$name.'.png');
    echo $name.': '.imagesx($cropped).'x'.imagesy($cropped).PHP_EOL;
    imagedestroy($cropped);
    imagedestroy($logo);
}
echo "Prepared original-brand logo and four WebP photographs.\n";
