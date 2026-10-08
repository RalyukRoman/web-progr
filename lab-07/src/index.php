<?php
require __DIR__ . '/bootstrap.php';

$isCorrectFavorites = 
    isset($_SESSION['favorites']) && 
    is_array($_SESSION['favorites']);

$favoritesCount = $isCorrectFavorites
    ? count($_SESSION['favorites'])
    : 0;

$rawTheme = $_COOKIE['club_theme'] ?? null;

$isCorrectTheme = 
    is_string($rawTheme) && 
    in_array($rawTheme, ['light', 'dark'], true);

$theme = $isCorrectTheme
    ? $rawTheme
    : 'light';
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Клуб "Web"</title>
    <style>
        body.light { background-color: #ffffff; color: #333333; }
        body.dark  { background-color: #222222; color: #ffffff; }
        nav a { margin-right: 15px; }
    </style>
</head>

<body class="<?= e($theme) ?>">
    <header>
        <h1>Клуб "Web"</h1>
        <nav>
            <a href="index.php">Головна</a>
            <a href="preferences.php">Налаштування</a>
            <a href="visits.php">Відвідування</a>
            <a href="favorites.php">Обране</a>
        </nav>
    </header>

    <br><hr>

    <main>
        <h2>Ласкаво просимо до нашого клубу!</h2>
        <p>Поточна тема оформлення: <strong><?= e($theme) ?></strong></p>

         <p>У вашому списку обраного зараз заходів: 
            <span class="badge"><?= e((string)$favoritesCount) ?></span>
        </p>
    </main>
</body>
</html>
