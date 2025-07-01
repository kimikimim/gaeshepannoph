<?php
session_start();
header("Content-Type: image/png");

$code = substr(str_shuffle("ABCDEFGHJKLMNPQRSTUVWXYZ23456789"), 0, 5);
$_SESSION['captcha'] = $code;

$img = imagecreatetruecolor(150, 50);
$bg = imagecolorallocate($img, 255, 255, 255);
$fg = imagecolorallocate($img, 0, 0, 0);
$line = imagecolorallocate($img, 220, 220, 220);

imagefill($img, 0, 0, $bg);

// 랜덤 선 추가 (스팸 방지)
for ($i = 0; $i < 5; $i++) {
    imageline($img, 0, rand() % 50, 150, rand() % 50, $line);
}

$font = __DIR__ . '/arial.ttf'; // 폰트 경로 (없으면 시스템 기본)
if (!file_exists($font)) {
    imagestring($img, 5, 30, 15, $code, $fg);
} else {
    imagettftext($img, 24, 0, 20, 35, $fg, $font, $code);
}

imagepng($img);
imagedestroy($img);
