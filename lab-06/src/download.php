<?php

define('SECURE_CORE', true);

require __DIR__ . '/common.php';
require __DIR__ . '/calculate_stats.php';


try {
    $path = selectedPath();
    $name = basename($path);
    $text = file_get_contents($path);
    
    if ($text === false) {
        throw new RuntimeException('Не вдалося прочитати файл');
    }

    $stats = calculateTextStats($text);

    $report = "ЗВІТ ПРО АНАЛІЗ ТЕКСТУ\n";
    $report .= "=====================\n";
    $report .= "Файл: " . $name . "\n";
    $report .= "Загальна кількість слів: " . $stats['total'] . "\n";
    $report .= "Кількість унікальних слів: " . $stats['unique'] . "\n";
    $report .= "Кількість непорожніх рядків: " . $stats['non_empty_lines'] . "\n";
    $report .= "\n";
    $report .= "ТОП-5 НАЙЧАСТІШИХ СЛІВ:\n";
    $report .= "---------------------\n";
    
    if (empty($stats['top'])) {
        $report .= "Слова відсутні\n";
    } else {
        foreach ($stats['top'] as $word => $count) {
            $report .= "- " . $word . ": " . $count . " раз(ів)\n";
        }
    }

    header('Content-Type: text/plain; charset=UTF-8');
    header('Content-Disposition: attachment; filename="report.txt"');
    echo $report;
    exit;

} catch (RuntimeException $exception) {
    http_response_code(400);
    header('Content-Type: text/plain; charset=UTF-8');
    echo "Помилка генерації звіту: " . $exception->getMessage();
    exit;
}
