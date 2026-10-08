<?php
declare(strict_types=1);
require __DIR__ . '/common.php';

$error = null;
$people = [];

try {
    $xmlPath = __DIR__ . '/persons.xml';
    $dom = parseXml(readText($xmlPath));
    
    $root = $dom->documentElement;
    if ($root === null) {
        throw new RuntimeException(
            'Кореневий елемент відсутній.'
        );
    }

    $newPerson = $dom->createElement('person');
    
    $newName = $dom->createElement('name');
    $newName->appendChild($dom->createTextNode('Іван'));
    
    $newAge = $dom->createElement('age');
    $newAge->appendChild($dom->createTextNode('30'));
    
    $newPerson->appendChild($newName);
    $newPerson->appendChild($newAge);
    
    $root->appendChild($newPerson);
    
    $bytes = $dom->save($xmlPath);
    if ($bytes === false) {
        throw new RuntimeException(
            'Не вдалося зберегти XML.'
        );
    }

    $people = peopleFromXml($dom);

} catch (RuntimeException $exception) {
    $error = $exception->getMessage();
}

header('Content-Type: text/html; charset=UTF-8');
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="utf-8">
    <title>Оновлений XML</title>
</head>

<body>
    <h1>Результат додавання запису в XML</h1>
    <?php if ($error !== null): ?>
        <?php http_response_code(500); ?>
        <p style="color: red;"><?= e($error) ?></p>
    <?php else: ?>
        <p style="color: green;">
            Запис успішно додано через DOMDocument!
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
