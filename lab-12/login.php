<?php
session_start();
$dbFile = __DIR__ . '/sqlite.db';

$error = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($login) && !empty($password)) {
        try {
            $pdo = new PDO("sqlite:" . $dbFile);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $sql = "SELECT * FROM users WHERE login = :login";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':login', $login);
            $stmt->execute();

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['login'] = $user['login'];

                header("Location: index.php");
                exit();
            } else {
                $error = "Невірне ім'я або пароль." . $login;

            }
        } catch (PDOException $e) {
            $error = "Помилка підключення до бази даних: " . $e->getMessage();
        }
    } else {
        $error = "Заповніть усі поля!";
    }
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Вхід у систему</title>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <h1>Авторизація користувача</h1>

    <?php if (isset($_SESSION['username'])): ?>
        <p>Ви вже увійшли як <strong><?= htmlspecialchars($_SESSION['username']) ?></strong>.</p>
        <a href="index.php">На головну</a>
    <?php else: ?>
        <?php if ($error): ?>
            <p style="color: red;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div>
                <label for="login">Логін:</label><br>
                <input type="text" name="login" required>
            </div>
            <br>
            <div>
                <label for="password">Пароль:</label><br>
                <input type="password" name="password" required>
            </div>
            <br>
            <button type="submit">Увійти</button>
        </form>

        <br>
        <a href="register.php">Ще не маєте акаунту? Зареєструватися</a> | 
        <a href="index.php">Повернутися</a>
    <?php endif; ?>
</body>
</html>