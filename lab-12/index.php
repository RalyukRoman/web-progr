<?php
$dbFile = __DIR__ . '/sqlite.db';

try {
    $pdo = new PDO("sqlite:" . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Помилка підключення до бази даних: " . $e->getMessage();
    exit();
}

$sql = "SELECT * FROM posts";
$stmt = $pdo->query($sql);
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Список статей</title>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <h1>Список статей блогу</h1>
    <?php if (count($posts) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Id</th>
                    <th>Заголовок</th>
                    <th>Перегляди</th>
                    <th>Дія</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($posts as $post): ?>
                    <tr>
                        <td><?= $post['id'] ?></td>
                        <td><?= htmlspecialchars($post['title']) ?></td>
                        <td><?= $post['views'] ?></td>
                        <td><a href="post.php?id=<?= $post['id'] ?>">Перейти</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>У блозі поки немає статей.</p>
    <?php endif; ?>
</body>
</html>