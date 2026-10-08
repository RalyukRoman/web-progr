<?php
declare(strict_types=1);
require __DIR__ . '/common.php';

$error = null;
$rows = [];

try {
    $dom = parseXml(readText(__DIR__ . '/persons.xml'));
    
    peopleFromXml($dom); 
    
    $counter = 1;
    foreach ($dom->getElementsByTagName('person') as $person) {
        $nameNode = $person->getElementsByTagName('name')->item(0);
        $ageNode = $person->getElementsByTagName('age')->item(0);
        
        $rows[] = [
            'id'   => $counter++,
            'name' => $nameNode ? $nameNode->textContent : '—',
            'age'  => $ageNode ? $ageNode->textContent : '—'
        ];
    }
} catch (RuntimeException $exception) {
    $error = $exception->getMessage();
}

header('Content-Type: text/html; charset=UTF-8');
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="utf-8">
    <title>Власний обхід DOM</title>
    <style>
        table  { border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 8px;}
        th     { background-color: #f4f4f4; }
    </style>
</head>

<body>
    <h1>Обхід дерева DOM вручну</h1>
    
    <?php if ($error !== null): ?>
        <?php http_response_code(500); ?>
        <p style="color: #d00000;"><?= e($error) ?></p>
    <?php elseif ($rows === []): ?>
        <p>Записів не знайдено.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Порядковий номер</th>
                    <th>Ім’я особи</th>
                    <th>Вік (років)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= e($row['name']) ?></td>
                        <td><?= e($row['age']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>
</html>
