<?php
session_start();

header('Content-type: image/png');

$text = $_SESSION['captcha_text'] ?? '0000';
$im = imagecreatetruecolor(100, 36);
$bg = imagecolorallocate($im, 255, 255, 255);
$fg = imagecolorallocate($im, 60, 60, 60);
imagefill($im, 0, 0, $bg);
imagestring($im, 5, 22, 10, $text, $fg);
imagepng($im);
imagedestroy($im);
