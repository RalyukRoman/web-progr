<?php
declare(strict_types=1);
require __DIR__ . '/common.php';

runImage(function (): void {
    [$width, $height] = chartSize();
    $image = canvas($width, $height);

    $values = [50, 25, 25];
    $colors = [[37, 99, 235], [22, 163, 74], [234, 88, 12]];

    $sum = array_sum($values);

    if ($sum <= 0) {
        throw new InvalidArgumentException(
            "Сума значень повинна бути більшою за нуль."
        );
    }

    foreach ($values as $value) {
        if ($value < 0) {
            throw new InvalidArgumentException(
                "Значення не можуть бути від'ємними."
            );
        }
    }

    $cx = (int) round($width / 2);
    $cy = (int) round($height / 2);
    $diameter = min($width, $height) - 100;

    $start = 0;
    $running = 0;
    foreach ($values as $i => $value) {
        $running += $value;
        $end = (int) round($running / $sum * 360);
        $color = imagecolorallocate($image, ...$colors[$i]);
        imagefilledarc($image, $cx, $cy, $diameter, $diameter, $start, $end, $color, IMG_ARC_PIE);
        $start = $end;
    }

    $text = imagecolorallocate($image, 20, 20, 20);

    $sharesText = sprintf("Shares: %d%% / %d%% / %d%%", 
        round(($values[0]/$sum)*100), 
        round(($values[1]/$sum)*100), 
        round(($values[2]/$sum)*100)
    );

    imagestring($image, 4, 15, 15, $sharesText, $text);
    sendPng($image);
});
