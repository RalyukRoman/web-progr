<?php
session_start();
$dbFile = __DIR__ . '/sqlite.db';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$users = [];
$error = '';

try {
    $pdo = new PDO("sqlite:" . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "SELECT id, login, full_name, email FROM users ORDER BY id ASC";
    $stmt = $pdo->query($sql);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Помилка підключення до бази даних: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Список користувачів</title>
</head>
<body>
    <?php include 'header.php'; ?>

    <h1>Список користувачів системи</h1>

    <?php if ($error): ?>
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <?php if (count($users) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Id</th>
                    <th>Логін</th>
                    <th>Повне ім'я</th>
                    <th>Email</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= $user['id'] ?></td>
                        <td><?= htmlspecialchars($user['login']) ?></td>
                        <td><?= htmlspecialchars($user['full_name'])  ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Користувачів не знайдено.</p>
    <?php endif; ?>
</body>
</html>