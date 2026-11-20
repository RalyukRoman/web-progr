<?php
$dbFile = __DIR__ . '/sqlite.db';

$message = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $login     = trim($_POST['login'] ?? '');
    $password  = $_POST['password'] ?? '';
    $full_name = trim($_POST['full_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');

    if (!empty($login) && !empty($email) && !empty($password)) {
        try {
            $pdo = new PDO("sqlite:" . $dbFile);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $sql = "
                INSERT INTO users (login, password, full_name, email)
                VALUES (:login, :password, :full_name, :email)
            ";

            $stmt = $pdo->prepare($sql);
            
            $stmt->bindParam(':login', $login);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':full_name', $full_name);
            $stmt->bindParam(':email', $email);

            if ($stmt->execute()) {
                $message = "Користувача зареєстровано!";
            } else {
                $error = "Помилка при додаванні.";
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
    <title>Реєстрація користувача</title>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <h1>Реєстрація нового користувача</h1>

    <?php if ($message): ?>
        <p style="color: green;"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <?php if ($error): ?>
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST" action="register.php">
        <div>
            <label for="login">Ім'я користувача:</label><br>
            <input type="text" name="login" required>
        </div>
        <br>
        <div>
            <label for="password">Пароль:</label><br>
            <input type="password" name="password" required>
        </div>
        <br>
        <div>
            <label for="full_name">Повне ім'я:</label><br>
            <input type="full_name" name="full_name" required>
        </div>
        <br>
        <div>
            <label for="email">Email:</label><br>
            <input type="email" name="email" required>
        </div>
        <br>
        <button type="submit">Зареєструватися</button>
    </form>

    <br>
    <a href="index.php">Повернутися</a>
</body>
</html>