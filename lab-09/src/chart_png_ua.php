<?php
declare(strict_types=1);

require __DIR__ . '/common.php';

function loadChartDataUa(): array
{
    $filePath = __DIR__ . '/chart-data-ua.json';
    if (!is_file($filePath) || !is_readable($filePath)) {
        throw new RuntimeException(
            'Не вдалося знайти або прочитати файл даних діаграми.'
        );
    }

    $text = file_get_contents($filePath);
    if ($text === false) {
        throw new RuntimeException(
            'Не вдалося прочитати дані діаграми.'
        );
    }

    $data = json_decode($text, true, 32, JSON_THROW_ON_ERROR);
    
    if (!str_starts_with(ltrim($text), '[') || !is_array($data) || 
        !array_is_list($data) || count($data) < 3 || count($data) > 10
    ){
        throw new RuntimeException(
            'Очікується список із 3–10 категорій.'
        );
    }

    foreach ($data as $row) {
        if (!is_array($row) || !isset($row['label'], $row['value']) || 
            !is_string($row['label']) || !is_int($row['value']) || 
            $row['value'] < 0 || $row['value'] > 1000000
        ){
            throw new RuntimeException(
                'Некоректна структура полів або вихід значень за межі.'
            );
        }

        if (!preg_match('/^[A-ZА-ЯҐЄІЇ][a-zA-Z0-9 а-яієїґщьюяА-ЯІЄЇҐЩЬЮЯ]{0,14}$/u', $row['label'])) {
            throw new RuntimeException(
                "Мітка '{$row['label']}' має починатися з великої літери 
                 та містити лише дозволені символи."
            );
        }
    }
    return $data;
}

runImage(function (): void {
    [$width, $height] = chartSize();
    $data = loadChartDataUa();
    
    $fontPath = getenv('LAB09_FONT') ?: __DIR__ . '/fonts/DejaVuSans.ttf';

    if (!is_file($fontPath) && PHP_OS_FAMILY === 'Windows') {
        $fontPath = 'C:/Windows/Fonts/arial.ttf';
    }

    if (!is_file($fontPath) || !is_readable($fontPath)) {
        throw new RuntimeException(
            'TrueType шрифт не знайдено або він недоступний для читання.'
        );
    }

    $image = canvas($width, $height);

    $bgGrid = imagecolorallocate($image, 241, 245, 249);
    $inkAxis = imagecolorallocate($image, 30, 41, 59);
    $inkText = imagecolorallocate($image, 71, 85, 105);
    $barColor = imagecolorallocate($image, 37, 99, 235); 

    $leftPadding = 65;
    $rightPadding = 30;
    $topPadding = 50;
    $bottomPadding = 75;

    $plotHeight = $height - $topPadding - $bottomPadding;
    $plotWidth = $width - $leftPadding - $rightPadding;

    $maxVal = max(array_column($data, 'value'));
    $scaleMax = max(1, $maxVal);

    imageline(
        $image, 
        $leftPadding, $topPadding, 
        $leftPadding, $height - $bottomPadding, 
        $inkAxis
    );

    imageline(
        $image, 
        $leftPadding, $height - $bottomPadding, 
        $width - $rightPadding, $height - $bottomPadding, 
        $inkAxis
    );

    $gridLevels = [0, 0.5, 1.0];
    foreach ($gridLevels as $level) {
        $yGrid = (int)($height - $bottomPadding - ($level * $plotHeight));
        imageline($image, $leftPadding, $yGrid, $width - $rightPadding, $yGrid, $bgGrid);
        
        $yLabel = (string)round($level * $scaleMax);
        imagettftext($image, 9, 0, $leftPadding - 55, $yGrid + 4, $inkAxis, $fontPath, $yLabel);
    }

    $count = count($data);
    $slotWidth = $plotWidth / $count;
    $barWidthRatio = 0.65;

    $labelFontSize = $width < 550 ? 8 : 10;

    foreach ($data as $i => $row) {
        $xCenter = $leftPadding + ($i * $slotWidth) + ($slotWidth / 2);

        $x1 = (int)($xCenter - ($slotWidth * $barWidthRatio) / 2);
        $x2 = (int)($xCenter + ($slotWidth * $barWidthRatio) / 2);
        
        $barHeight = (int)(($row['value'] / $scaleMax) * $plotHeight);
        $y1 = $height - $bottomPadding - $barHeight;
        $y2 = $height - $bottomPadding;

        if ($barHeight > 0) {
            imagefilledrectangle(
                $image, $x1, $y1, 
                $x2, $y2 - 1, 
                $barColor
            );
        }

        $valText = (string)$row['value'];
        $valBox = imagettfbbox(9, 0, $fontPath, $valText);
        $valTextWidth = $valBox[2] - $valBox[0];

        imagettftext(
            $image, 9, 0, 
            (int)($xCenter - $valTextWidth / 2), 
            $y1 - 7, 
            $inkAxis, $fontPath, $valText
        );

        $label = $row['label'];
        $labelBox = imagettfbbox($labelFontSize, 0, $fontPath, $label);
        $labelTextWidth = $labelBox[2] - $labelBox[0];

        if ($labelTextWidth > $slotWidth && mb_strlen($label) > 6) {
            $label = mb_substr($label, 0, 5) . '...';
            $labelBox = imagettfbbox($labelFontSize, 0, $fontPath, $label);
            $labelTextWidth = $labelBox[2] - $labelBox[0];
        }

        imagettftext(
            $image, $labelFontSize, 0, 
            (int)($xCenter - $labelTextWidth / 2), 
            $height - $bottomPadding + 22, 
            $inkAxis, $fontPath, $label
        );
    }

    $group = "451";
    $authorName = "Ралюк Роман Вікторович"; 
    $dateStr = date('d.m.Y');
    $footerText = "Група: {$group} | Автор: {$authorName} | Дата: {$dateStr}";

    $footerFontSize = 9;
    $footerBox = imagettfbbox($footerFontSize, 0, $fontPath, $footerText);
    $footerWidth = $footerBox[2] - $footerBox[0];
    
    $footerX = $width - $footerWidth - $rightPadding;
    $footerY = $height - 15;

    imagettftext(
        $image, $footerFontSize, 0, 
        $footerX, $footerY, 
        $inkText, $fontPath, $footerText
    );

    imagettftext(
        $image, 13, 0, 
        $leftPadding, 25, 
        $inkAxis, $fontPath,
        "Статистика використання навчальних ресурсів"
    );

    sendPng($image);
});