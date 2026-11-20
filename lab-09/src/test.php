<?php
if (!function_exists('gd_info')) {
    echo "Бібліотека GD взагалі не встановлена на цьому сервері!";
    exit;
}

$gdData = gd_info();

if ($gdData['WebP Support']) {
    echo "Чудово! Сервер підтримує генерацію WebP зображень.";
} else {
    echo "Формат WebP не підтримується. Використовуйте JPEG або PNG.";
}
?>