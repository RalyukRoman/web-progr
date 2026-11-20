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
            'Очікується список із 3-10 категорій.'
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
                "Мітка '{$row['label']}' має починатися з великої літери."
            );
        }
    }
    return $data;
}

runImage(function (): void {
    [$width, $height] = chartSize();
    $data = loadChartDataUa();

    $leftPadding = 65;
    $rightPadding = 30;
    $topPadding = 50;
    $bottomPadding = 75;

    $plotHeight = $height - $topPadding - $bottomPadding;
    $plotWidth = $width - $leftPadding - $rightPadding;

    $maxVal = max(array_column($data, 'value'));
    $scaleMax = max(1, $maxVal);

    $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' . $width . ' ' . $height . '" '
        . 'width="' . $width . '" height="' . $height . '" role="img" aria-labelledby="title desc">';
    
    $svg .= '<title id="title">Статистика використання навчальних ресурсів</title>';
    $svg .= '<desc id="desc">Векторна стовпчаста діаграма навчальної активності студентів.</desc>';
    
    $svg .= '<rect width="100%" height="100%" fill="#ffffff"/>';

    $gridLevels = [0, 0.5, 1.0];
    foreach ($gridLevels as $level) {
        $yGrid = (int)($height - $bottomPadding - ($level * $plotHeight));
        $svg .= '<line x1="' . $leftPadding . '" y1="' . $yGrid . '" x2="' . ($width - $rightPadding) . '" y2="' . $yGrid . '" stroke="#f1f5f9" stroke-width="1"/>';
    }

    $svg .= '<path d="M ' . $leftPadding . ' ' . $topPadding . ' V ' . ($height - $bottomPadding) . ' H ' . ($width - $rightPadding) . '" fill="none" stroke="#1e293b" stroke-width="1"/>';

    $svg .= '<g font-family="sans-serif" font-size="12" fill="#1e293b">';
    foreach ($gridLevels as $level) {
        $yGrid = (int)($height - $bottomPadding - ($level * $plotHeight));
        $yLabel = (string)round($level * $scaleMax);
        $svg .= '<text x="' . ($leftPadding - 12) . '" y="' . ($yGrid + 4) . '" text-anchor="end">' . $yLabel . '</text>';
    }
    $svg .= '</g>';

    $svg .= '<text x="' . $leftPadding . '" y="28" font-family="sans-serif" font-size="15" font-weight="bold" fill="#1e293b">' 
        . xmlText("Статистика використання навчальних ресурсів") . '</text>';

    $count = count($data);
    $slotWidth = $plotWidth / $count;
    $barWidthRatio = 0.65;
    $labelFontSize = $width < 550 ? 11 : 13;

    foreach ($data as $i => $row) {
        $xCenter = $leftPadding + ($i * $slotWidth) + ($slotWidth / 2);
        $x1 = (int)($xCenter - ($slotWidth * $barWidthRatio) / 2);
        $barWidth = (int)($slotWidth * $barWidthRatio);
        
        $barHeight = (int)(($row['value'] / $scaleMax) * $plotHeight);
        $y1 = $height - $bottomPadding - $barHeight;

        if ($barHeight > 0) {
            $svg .= '<rect x="' . $x1 . '" y="' . $y1 . '" width="' . $barWidth . '" height="' . $barHeight . '" fill="#2563eb"/>';
        }

        $svg .= '<text x="' . $xCenter . '" y="' . ($y1 - 7) . '" font-family="sans-serif" font-size="11" fill="#1e293b" text-anchor="middle">' 
            . $row['value'] 
            . '</text>';

        $label = $row['label'];
        if (mb_strlen($label) > 8 && $slotWidth < 80) {
            $label = mb_substr($label, 0, 6) . '...';
        }

        $svg .= '<text x="' . $xCenter . '" y="' . ($height - $bottomPadding + 22) . '" font-family="sans-serif" font-size="' . $labelFontSize . '" fill="#1e293b" text-anchor="middle">' 
            . xmlText($label) 
            . '</text>';
    }

    $group = "451";
    $authorName = "Ралюк Роман Вікторович";
    $dateStr = date('d.m.Y');
    $footerText = "Група: {$group} | Автор: {$authorName} | Дата: {$dateStr}";

    $svg .= '<text x="' . ($width - $rightPadding) . '" y="' . ($height - 15) . '" font-family="sans-serif" font-size="11" fill="#475569" text-anchor="end">' 
        . xmlText($footerText) 
        . '</text>';

    $svg .= '</svg>';

    header('Content-Type: image/svg+xml; charset=UTF-8');
    header('X-Content-Type-Options: nosniff');
    
    echo $svg;
});