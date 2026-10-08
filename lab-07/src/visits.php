<?php
require __DIR__ . '/bootstrap.php';

date_default_timezone_set('Europe/Kyiv');

$cookieError = null;
$showWelcome = false;

$rawVisits = $_COOKIE['club_visits'] ?? null;
$rawLastVisit = $_COOKIE['club_last_visit'] ?? null;

$currentVisits = 0;

if ($rawVisits === null) {
    $showWelcome = true;
} else {
    $isCorrectVisits = 
        is_string($rawVisits) && 
        ctype_digit($rawVisits);

    if ($isCorrectVisits) {
        $validatedInt = filter_var($rawVisits, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 0, 'max_range' => 999999]
        ]);
        
        if ($validatedInt !== false) {
            $currentVisits = $validatedInt;
        } else {
            $showWelcome = true;
        }
    } else {
        $showWelcome = true;
    }
}

$previousTime = null;
$currentTime = time();

$isCorrectLastVisits = 
    is_string($rawLastVisit) && 
    ctype_digit($rawLastVisit);

if ($isCorrectLastVisits) {
    $validatedTime = filter_var($rawLastVisit, FILTER_VALIDATE_INT, [
        'options' => [
            'min_range' => 1, 
            'max_range' => $currentTime
        ]
    ]);
    
    if ($validatedTime !== false) {
        $previousTime = $validatedTime;
    }
}

$newVisits = $currentVisits + 1;

if ($newVisits > 999999) {
    $newVisits = 999999;
}

$expires = $currentTime + 604800;

$successVisits    = setcookie('club_visits',     (string)$newVisits,   cookieOptions($expires));
$successLastVisit = setcookie('club_last_visit', (string)$currentTime, cookieOptions($expires));

if (!$successVisits || !$successLastVisit) {
    $cookieError = 'Помилка підготовки HTTP-заголовків.';
}

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
    <title>Лічильник відвідувань</title>
    <style>
        body.light { background-color: #ffffff; color: #333333; }
        body.dark  { background-color: #222222; color: #ffffff; }
        nav a      { margin-right: 15px; }

        .box { 
            padding: 15px; 
            border-radius: 5px; 
            max-width: 400px; 
            margin-top: 20px; 
        }

        body.light .box { background: rgba(0,0,0,0.05); }
        body.dark  .box { background: rgba(255,255,255,0.1); }
    </style>
</head>

<body class="<?= e($theme) ?>">
    <header>
        <h1>Статистика відвідувань сторінки</h1>
        <nav>
            <a href="index.php">Головна</a>
            <a href="preferences.php">Налаштування</a>
            <a href="visits.php">Відвідування</a>
            <a href="favorites.php">Обране</a>
        </nav>
    </header>

    <main>
        <?php if ($cookieError !== null): ?>
            <p style="color: #d00000;"><?= e($cookieError) ?></p>
        <?php endif; ?>

        <?php if ($showWelcome || $newVisits === 1): ?>
            <p>Ласкаво просимо!</p>
        <?php endif; ?>

        <div class="box">
            <p>Номер поточного перегляду: 
                <strong><?= e((string)$newVisits) ?></strong>
            </p>
            
            <p>Час попереднього перегляду: 
                <strong>
                    <?php if ($previousTime !== null): ?>
                        <?= e(date('d.m.Y H:i:s', $previousTime)) ?>
                    <?php else: ?>
                        Попередній час недоступний
                    <?php endif; ?>
                </strong>
            </p>
        </div>
    </main>
</body>
</html>
