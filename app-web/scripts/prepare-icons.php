<?php

// Derives square site icons from the isotype (lungs and rod) of the original logo. The logo files are not changed.
$target = dirname(__DIR__).'/public';
$logo = imagecreatefrompng($target.'/images/logo.png');
$limit = (int) (imagesx($logo) * 0.3); // The isotype sits in the left part of the wordmark.
$left = $limit; $top = imagesy($logo); $right = $bottom = -1;
for ($y = 0; $y < imagesy($logo); $y++) {
    for ($x = 0; $x < $limit; $x++) {
        if (((imagecolorat($logo, $x, $y) >> 24) & 0x7f) < 120) {
            $left = min($left, $x); $right = max($right, $x); $top = min($top, $y); $bottom = max($bottom, $y);
        }
    }
}
$width = $right - $left + 1; $height = $bottom - $top + 1;
$side = (int) ceil(max($width, $height) * 1.08);
$square = imagecreatetruecolor($side, $side);
imagealphablending($square, false); imagesavealpha($square, true);
imagefill($square, 0, 0, imagecolorallocatealpha($square, 0, 0, 0, 127));
imagecopy($square, $logo, (int) (($side - $width) / 2), (int) (($side - $height) / 2), $left, $top, $width, $height);

function resized($source, int $size, bool $opaque = false)
{
    $image = imagecreatetruecolor($size, $size);
    imagealphablending($image, false); imagesavealpha($image, true);
    imagefill($image, 0, 0, $opaque ? imagecolorallocate($image, 255, 255, 255) : imagecolorallocatealpha($image, 0, 0, 0, 127));
    if ($opaque) { imagealphablending($image, true); }
    imagecopyresampled($image, $source, 0, 0, 0, 0, $size, $size, imagesx($source), imagesy($source));
    return $image;
}
function pngBytes($image): string { ob_start(); imagepng($image, null, 9); return ob_get_clean(); }

foreach ([512 => 'icon-512.png', 192 => 'icon-192.png', 32 => 'icon-32.png'] as $size => $file) {
    imagepng(resized($square, $size), $target.'/'.$file, 9);
}
// Apple touch icons are shown without transparency; use a white background with a small margin.
$apple = imagecreatetruecolor(180, 180);
imagefill($apple, 0, 0, imagecolorallocate($apple, 255, 255, 255));
imagealphablending($apple, true);
$inner = resized($square, 156);
imagecopy($apple, $inner, 12, 12, 0, 0, 156, 156);
imagepng($apple, $target.'/apple-touch-icon.png', 9);
// favicon.ico as an ICO container with PNG entries (supported by current browsers and Windows).
$entries = [];
foreach ([16, 32, 48] as $size) { $entries[$size] = pngBytes(resized($square, $size)); }
$header = pack('vvv', 0, 1, count($entries));
$directory = ''; $data = ''; $offset = 6 + 16 * count($entries);
foreach ($entries as $size => $png) {
    $directory .= pack('CCCCvvVV', $size, $size, 0, 0, 1, 32, strlen($png), $offset);
    $offset += strlen($png); $data .= $png;
}
file_put_contents($target.'/favicon.ico', $header.$directory.$data);
echo "Icons prepared from a {$width}x{$height} isotype crop.\n";
