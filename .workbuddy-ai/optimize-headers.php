<?php

// Convert the generated page-header PNGs to WebP.
// They are 1.5-2 MB each as PNG (~9 MB total) which is far too heavy for a
// background image; WebP at q82 keeps them visually identical at a fraction of it.
$dir = 'D:/education/LearningWeb/public/images/page-headers';

$totalBefore = 0;
$totalAfter = 0;

foreach (['about', 'blog', 'categories', 'contact', 'courses'] as $name) {
    $src = "$dir/$name.png";
    $dst = "$dir/$name.webp";

    if (! is_file($src)) {
        printf("%-12s MISSING\n", $name);
        continue;
    }

    $img = imagecreatefrompng($src);
    $width = imagesx($img);
    $height = imagesy($img);

    // Never upscale; 1536px is already wider than the band needs.
    if ($width > 1920) {
        $img = imagescale($img, 1920, (int) round($height * 1920 / $width), IMG_BICUBIC);
    }

    imagewebp($img, $dst, 82);
    imagedestroy($img);

    $before = filesize($src);
    $after = filesize($dst);
    $totalBefore += $before;
    $totalAfter += $after;

    printf("%-12s %7.0f KB -> %7.0f KB  (%dx%d)\n", $name, $before / 1024, $after / 1024, $width, $height);
}

printf("%-12s %7.0f KB -> %7.0f KB\n", 'TOTAL', $totalBefore / 1024, $totalAfter / 1024);
