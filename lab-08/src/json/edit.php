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
$id = $_GET['id'] ?? ($_POST['id'] ?? '');
$id = is_string($id) ? trim($id) : '';

$title = '';
$content = '';
$views = '';
$postFound = false;

$jsonPath = __DIR__ . '/posts.json';

try {
    if ($id === '') {
        throw new RuntimeException('Ідентифікатор запису не вказано.');
    }

    ensureJsonFileExists($jsonPath);
    $posts = postsFromJson(readText($jsonPath));

    $targetIndex = null;
    foreach ($posts as $index => $post) {
        if ($post['id'] === $id) {
            $targetIndex = $index;
            break;
        }
    }

    if ($targetIndex === null) {
        throw new RuntimeException('Запис із вказаним id не знайдено.');
    }

    $postFound = true;
    $title = $posts[$targetIndex]['title'];
    $content = $posts[$targetIndex]['content'];
    $views = (string)$posts[$targetIndex]['views'];

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

        $inputTitle   = is_string($rawTitle)   ? trim($rawTitle)   : '';
        $inputContent = is_string($rawContent) ? trim($rawContent) : '';

        $title   = $inputTitle;
        $content = $inputContent;

        $titleLength = mb_strlen($inputTitle, 'UTF-8');
        if ($titleLength < 5 || $titleLength > 100) {
            throw new RuntimeException('Заголовок має містити від 5 до 100 символів.');
        }

        $contentLength = mb_strlen($inputContent, 'UTF-8');
        if ($contentLength < 10 || $contentLength > 5000) {
            throw new RuntimeException('Текст статті має містити від 10 до 5000 символів.');
        }

        $posts[$targetIndex]['title']   = $inputTitle;
        $posts[$targetIndex]['content'] = $inputContent;

        $jsonText = postsToJson($posts);

        if (file_put_contents($jsonPath, $jsonText) === false) {
            throw new RuntimeException('Не вдалося записати зміни у файл.');
        }

        $_SESSION['flash_message'] = 'Запис успішно відредаговано!';
        header('Location: index.php', true, 303);
        exit;
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
    <title>Редагувати запис (JSON)</title>
    <style>
        body { padding: 0 15px; }

        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; }
        .form-group input[type="text"] { width: 100%; }
        .form-group textarea { width: 100%; height: 50px; }

        .btn-submit { 
            background: #107d35; 
            color: white; 
            padding: 10px 15px;
            border: none; 
            cursor: pointer;
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
    <h1>Редагувати запис блогу (JSON-версія)</h1>

    <?php if ($error !== null): ?>
        <p style="color: #d00000; font-weight: bold;"><?= e($error) ?></p>
    <?php endif; ?>

    <?php if ($postFound): ?>
        <form action="edit.php?id=<?= e(urlencode($id)) ?>" method="POST">
            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
            <input type="hidden" name="id" value="<?= e($id) ?>">

            <div class="form-group">
                <label>Ідентифікатор (id):</label>
                <input type="text" value="<?= e($id) ?>" disabled>
            </div>

            <div class="form-group">
                <label for="title-field">Заголовок (від 5 до 100 символів):</label>
                <input type="text" id="title-field" name="title" value="<?= e($title) ?>" required>
            </div>

            <div class="form-group">
                <label for="content-field">Текст статті (від 10 до 5000 символів):</label>
                <textarea id="content-field" name="content" required><?= e($content) ?></textarea>
            </div>

            <div>
                <button type="submit" class="btn-submit">Зберегти зміни</button>
                <a href="index.php" class="btn-back">Скасувати</a>
            </div>
        </form>
    <?php else: ?>
        <p>
            <a href="index.php" class="btn-back">Повернутися до списку</a>
        </p>
    <?php endif; ?>
</body>
</html>