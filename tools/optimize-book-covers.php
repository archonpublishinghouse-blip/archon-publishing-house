<?php
// Local asset build: php -d extension=gd tools/optimize-book-covers.php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
if (!function_exists('imagewebp')) throw new RuntimeException('Enable the GD extension with WebP support for this asset build.');
$directory = dirname(__DIR__) . '/public/assets/images/published-books';
$names = ['words-that-outlive-us', 'from-vision-to-volume', 'the-unwritten-legacy', 'echoes-of-ambition', 'the-courage-to-begin'];
foreach ($names as $name) {
    $original = $directory . '/' . $name . '.png';
    $source = imagecreatefrompng($original);
    if (!$source) throw new RuntimeException('Could not decode ' . $name);
    foreach ([480, 960] as $width) {
        $height = (int)round(imagesy($source) * $width / imagesx($source));
        $output = imagecreatetruecolor($width, $height);
        imagealphablending($output, false);
        imagesavealpha($output, true);
        imagecopyresampled($output, $source, 0, 0, 0, 0, $width, $height, imagesx($source), imagesy($source));
        $path = $directory . '/' . $name . '-' . $width . '.webp';
        if (!imagewebp($output, $path, 84)) throw new RuntimeException('Could not encode ' . $name);
        imagedestroy($output);
        printf("%s: %dx%d, %d bytes (original %d bytes)\n", basename($path), $width, $height, filesize($path), filesize($original));
    }
    imagedestroy($source);
}
