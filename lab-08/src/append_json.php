<?php
declare(strict_types=1);
require __DIR__ . '/common.php';

$error = null;
$people = [];

try {
    $jsonPath = __DIR__ . '/persons.json';
    $json = readText($jsonPath);
    
    $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
    
    $people = validatePeople($decoded);
    $people[] = ['name' => 'Марія', 'age' => 22];
    
    $newJson = json_encode(
        validatePeople($people), 
        JSON_UNESCAPED_UNICODE | 
        JSON_PRETTY_PRINT      | 
        JSON_THROW_ON_ERROR
    );
    
    $content = $newJson . "\n";
    
    $bytes = file_put_contents($jsonPath, $content, LOCK_EX);
    if ($bytes === false || $bytes !== strlen($content)) {
        throw new RuntimeException(
            'Не вдалося повністю записати JSON.'
        );
    }

} catch (JsonException $exception) {
    $error = 'Некоректний JSON або кодування UTF-8.';
} catch (RuntimeException $exception) {
    $error = $exception->getMessage();
}

header('Content-Type: text/html; charset=UTF-8');
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="utf-8">
    <title>Оновлений JSON</title>
</head>

<body>
    <h1>Результат додавання запису в JSON</h1>
    <?php if ($error !== null): ?>
        <?php http_response_code(500); ?>
        <p style="color: red"><?= e($error) ?></p>
    <?php else: ?>
        <p style="color: green;">
            Запис успішно додано через PHP-масив!
        </p>
        
        <table>
            <thead>
                <tr>
                    <th>Ім’я</th>
                    <th>Вік</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($people as $person): ?>
                    <tr>
                        <td><?= e($person['name']) ?></td>
                        <td><?= $person['age'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>
</html>
