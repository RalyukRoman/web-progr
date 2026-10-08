<?php
declare(strict_types=1);
require __DIR__ . '/common.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = null;
$oldTitle = '';
$oldContent = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';

    if (!is_string($token) || empty($_SESSION['csrf_token']) || 
        !hash_equals($_SESSION['csrf_token'], $token)
    ) {
        http_response_code(403);
        exit('Помилка CSRF: запит відхилено.');
    }

    $rawTitle   = $_POST['title'] ?? '';
    $rawContent = $_POST['content'] ?? '';

    $oldTitle   = is_string($rawTitle)   ? $rawTitle : '';
    $oldContent = is_string($rawContent) ? $rawContent : '';

    $title   = trim($oldTitle);
    $content = trim($oldContent);

    try {
        $titleLength = mb_strlen($title, 'UTF-8');
        if ($titleLength < 5 || $titleLength > 100) {
            throw new RuntimeException('Заголовок має містити від 5 до 100 символів.');
        }

        $contentLength = mb_strlen($content, 'UTF-8');
        if ($contentLength < 10 || $contentLength > 5000) {
            throw new RuntimeException('Текст статті має містити від 10 до 5000 символів.');
        }

        $jsonPath = __DIR__ . '/posts.json';
        ensureJsonFileExists($jsonPath);
        
        $posts = postsFromJson(readText($jsonPath));

        $existingIds = array_column($posts, 'id');

        do {
            $newId = bin2hex(random_bytes(8));
        } while (in_array($newId, $existingIds, true));

        $posts[] = [
            'id'      => $newId,
            'title'   => $title,
            'content' => $content,
            'views'   => 0,
        ];

        $jsonText = postsToJson($posts);

        if (file_put_contents($jsonPath, $jsonText) === false) {
            throw new RuntimeException('Не вдалося записати зміни у файл.');
        }

        $_SESSION['flash_message'] = 'Запис успішно додано до блогу!';
        header('Location: index.php', true, 303);
        exit;
    } catch (RuntimeException $exception) {
        $error = $exception->getMessage();
    }
}

header('Content-Type: text/html; charset=UTF-8');
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="utf-8">
    <title>Додати запис (JSON)</title>
    <style>
        body { padding: 0 15px; }

        .form-group { margin-bottom: 15px; }
        .form-group input[type="text"] { width: 100%; }
        .form-group textarea { width: 100%; height: 50px; }
        .form-group label { display: block; margin-bottom: 5px; }

        .btn-submit { 
            background: #107d35; 
            color: white; 
            padding: 10px 15px;
            border: none;
        }
        
        .btn-back { 
            margin-left: 10px; 
            color: #555;
            border: #555 solid 1px;
            padding: 5px 15px;
            text-decoration: none;
        }
    </style>
</head>

<body>
    <h1>Новий запис блогу (JSON-версія)</h1>

    <?php if ($error !== null): ?>
        <p style="color: #d00000;"><?= e($error) ?></p>
    <?php endif; ?>

    <form action="add.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">

        <div class="form-group">
            <label for="title-field">Заголовок (від 5 до 100 символів):</label>
            <input type="text" id="title-field" name="title" value="<?= e($oldTitle) ?>" required>
        </div>

        <div class="form-group">
            <label for="content-field">Текст статті (від 10 до 5000 символів):</label>
            <textarea id="content-field" name="content" required><?= e($oldContent) ?></textarea>
        </div>

        <div>
            <button type="submit" class="btn-submit">Зберегти запис</button>
            <a href="index.php" class="btn-back">Скасувати</a>
        </div>
    </form>
</body>
</html>