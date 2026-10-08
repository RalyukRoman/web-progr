<?php
$word = "Привіт";

$bytes_count = strlen($word);
$chars_count = mb_strlen($word, 'UTF-8');
?>

<!doctype html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Порівняння strlen та mb_strlen</title>
</head>
<body>
    <p>Досліджуване слово: <strong><?= htmlspecialchars($word) ?></strong></p>

    <table style="border-spacing: 20px 5px;">
        <thead>
            <tr>
                <th>Функція</th>
                <th>Результат</th>
                <th>Що саме рахує</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>strlen($word)</td>
                <td><?= $bytes_count ?></td>
                <td>Кількість байтів</td>
            </tr>
            <tr>
                <td>mb_strlen($word, 'UTF-8')</td>
                <td><?= $chars_count ?></td>
                <td>Кількість фактичних символів</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
