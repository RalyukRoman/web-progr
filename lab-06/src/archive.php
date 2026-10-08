<?php
require __DIR__ . '/common.php';

$error = '';
$files_list = [];

try {
    $files = glob(storageDir() . '/*.txt');
    
    if ($files === false) {
        throw new RuntimeException('Не вдалося прочитати директорію даних');
    }
    
    foreach ($files as $filepath) {
        if (is_file($filepath)) {
            $filename = basename($filepath);
            $size = filesize($filepath);
            
            $files_list[] = [
                'name' => $filename,
                'size' => $size !== false ? $size : 0
            ];
        }
    }
    
    rsort($files_list);

} catch (RuntimeException $exception) {
    http_response_code(500);
    $error = $exception->getMessage();
}
?>

<!doctype html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Архів ідей</title>
</head>
<body>
    <h1>Архів ідей студентського клубу</h1>

    <?php if ($error !== ''): ?>
        <p role="alert"><?= e($error) ?></p>
    <?php else: ?>
        <?php if (empty($files_list)): ?>
            <p>Архів порожній</p>
        <?php else: ?>
            <table style="border-spacing: 20px 5px;">
                <thead>
                    <tr>
                        <th>Назва файлу</th>
                        <th>Розмір (байтів)</th>
                        <th>Дії</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($files_list as $file): ?>
                        <tr>
                            <td><?= e($file['name']) ?></td>
                            <td><?= $file['size'] ?></td>
                            <td>
                                <a href="read_file.php?file=<?= rawurlencode($file['name']) ?>">Переглянути</a> 
                                · 
                                <a href="analyze.php?file=<?= rawurlencode($file['name']) ?>">Аналізувати</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    <?php endif; ?>

    <p class="actions">
        <a href="write_1.php">Нова нотатка</a> 
        · 
        <a href="upload.php">Імпорт</a>
    </p>
</body>
</html>
