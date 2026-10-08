<?php
declare(strict_types=1);
require __DIR__ . '/common.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$posts = [];
$error = null;

try {
    $xmlPath = __DIR__ . '/posts.xml';
    ensureXmlFileExists($xmlPath);
    $dom = parseXml(readText($xmlPath));
    $posts = postsFromXml($dom);
} 
catch (RuntimeException $exception) {
    $error = $exception->getMessage();
}

$flashMessage = $_SESSION['flash_message'] ?? null;

if ($flashMessage !== null) {
    unset($_SESSION['flash_message']);
}

header('Content-Type: text/html; charset=UTF-8');
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="utf-8">
    <title>Власний блог (XML)</title>
    <style>
        body { padding: 0 15px; }

        table  { border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ccc; padding: 10px; }
        th     { background-color: #f4f4f4; }

        .flash-success {
            background-color: #eff8f1; 
            color: #0d7e33; 
            padding: 10px; 
            border: 1px solid #0d7e33; 
            margin-bottom: 15px; 
        }
        
        .btn-edit {
            color: #0056b3;
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <h1>Публікації мого блогу (XML-версія)</h1>

    <?php if ($flashMessage !== null): ?>
        <div class="flash-success"><?= e($flashMessage) ?></div>
    <?php endif; ?>

    <?php if ($error !== null): ?>
        <?php http_response_code(500); ?>
        <p style="color: #d00000;"><?= e($error) ?></p>
    <?php else: ?>
        <p>
            <a href="add.php">+ Додати новий запис</a>
        </p>

        <?php if ($posts === []): ?>
            <p>Записів немає.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Ідентифікатор (id)</th>
                        <th>Заголовок статті (title)</th>
                        <th>Текст статті (content)</th>
                        <th>Перегляди (views)</th>
                        <th>Дії</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($posts as $post): ?>
                        <tr>
                            <td><code><?= e($post['id']) ?></code></td>
                            <td><strong><?= e($post['title']) ?></strong></td>
                            <td><?= nl2br(e($post['content'])) ?></td>
                            <td><?= e((string)$post['views']) ?></td>
                            <td>
                                <a class="btn-edit" href="edit.php?id=<?= e(urlencode($post['id'])) ?>">
                                    Редагувати
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    <?php endif; ?>
</body>
</html>