<?php
$dbFile = __DIR__ . '/sqlite.db';

$post = null;

$message = '';
$error = '';

if (isset($_GET['id'])) {
    try {
        $pdo = new PDO("sqlite:" . $dbFile);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $postId = $_GET['id'];
        $sql = "SELECT * FROM posts WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $postId, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $post = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$post) {
                $error = "Статтю з таким Id не знайдено.";
            }
        } else {
            $error = "Помилка при виборці інформації.";
        }
    } catch (PDOException $e) {
        $error = "Помилка підключення до бази даних: " . $e->getMessage();
    }
} else {
    $error = "Id не вказано.";
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($post['title']) ?></title>
</head>
<body>
    <?php include 'header.php'; ?>

    <?php if ($message): ?>
        <p style="color: green;"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <?php if ($error): ?>
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <?php if ($post): ?>
        <h1><?= htmlspecialchars($post['title']) ?></h1>
        <p>Переглядів: <?= $post['views'] ?></p>
        <div><?= nl2br(htmlspecialchars($post['content'])) ?></div>
    <?php endif; ?>

    <br>
    <a href="index.php">Повернутися</a>
</body>
</html>