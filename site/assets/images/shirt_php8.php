<?php
/**
 * SportsManagement Joomla 5/6 file metadata.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// This image endpoint is intentionally callable without the Joomla bootstrap.
// Limit untrusted query input and avoid emitting PHP warnings as image bytes.
$rawText = $_GET['text'] ?? '';
$string = is_string($rawText) && strlen($rawText) <= 64
    && preg_match('/^[\\x20-\\x7E]*$/D', $rawText) === 1
    ? substr($rawText, 0, 8)
    : '';

if (!function_exists('imagecreatefrompng') || !function_exists('imagepng')) {
    http_response_code(503);
    exit;
}

header('Content-Type: image/png');

$font    = 2;

$imageFile = __DIR__ . '/shirt_php8.png';

if (!is_file($imageFile)) {
    http_response_code(404);
    exit;
}

$image = imagecreatefrompng($imageFile);

if ($image === false) {
    http_response_code(500);
    exit;
}

$xpos = strlen($string) > 1 ? 9 : 12;

if (function_exists('imagesavealpha') && function_exists('imagealphablending')) {
    imagealphablending($image, false);
    imagesavealpha($image, true);
}

$textColor = imagecolorallocate($image, 0, 0, 0);

imagestring($image, $font, $xpos, 1, $string, $textColor);
imagepng($image);
imagedestroy($image);
