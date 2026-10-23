<?php

// Rebuild the language-neutral sharing card with GD: php scripts/generate-social-image.php
$font = null;
foreach (['C:/Windows/Fonts/arialbd.ttf', '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf'] as $candidate) {
    if (is_file($candidate)) {
        $font = $candidate;
        break;
    }
}
if (!$font) {
    throw new RuntimeException('Install Arial or DejaVu Sans Bold to rebuild the sharing card.');
}
$image = imagecreatetruecolor(1200, 630);
$navy = imagecolorallocate($image, 11, 38, 56);
$teal = imagecolorallocate($image, 92, 182, 190);
$white = imagecolorallocate($image, 244, 247, 246);
imagefill($image, 0, 0, $navy);
imagefilledrectangle($image, 100, 115, 200, 123, $teal);
$box = imagettfbbox(96, 0, $font, 'Daly');
imagettftext($image, 96, 0, 100, 340, $white, $font, 'Daly');
imagettftext($image, 96, 0, 100 + $box[2], 340, $teal, $font, 'Trac');
imagettftext($image, 24, 0, 105, 425, $white, $font, 'dalytrac.com');
imagepng($image, __DIR__.'/../public/img/dalytrac-social.png', 9);
imagedestroy($image);
