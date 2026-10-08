<?php
require __DIR__ . '/bootstrap.php';

$error = null;

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    requireValidPost();

    $action = $_POST['action'] ?? null;

    if ($action === 'save') {
        $chosenTheme = $_POST['theme'] ?? null;

        $isCorrectChosenTheme = 
            is_string($chosenTheme) && 
            in_array($chosenTheme, ['light', 'dark'], true);
        
        if ($isCorrectChosenTheme) {
            $expires = time() + 30 * 24 * 60 * 60;
            
            $success = setcookie(
                'club_theme', 
                $chosenTheme, 
                cookieOptions($expires)
            );
            
            if ($success) {
                header('Location: index.php', true, 303);
                exit;
            } else {
                $error = 'Не вдалося підготувати заголовки.';
            }
        } else {
            $error = 'Невідома або некоректна тема.';
        }
    } 
    elseif ($action === 'reset') {
        $success = setcookie(
            'club_theme', 
            '', 
            cookieOptions(time() - 3600)
        );
        
        if ($success) {
            header('Location: index.php', true, 303);
            exit;
        } else {
            $error = 'Не вдалося підготувати заголовки.';
        }
    } 
    else $error = 'Невідома дія.';
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
    <title>Налаштування теми</title>
    <style>
        body.light { background-color: #ffffff; color: #333333; }
        body.dark  { background-color: #222222; color: #ffffff; }
        nav a      { margin-right: 15px; }

        .form-group { 
            margin-bottom: 20px; 
            border: 1px solid #ccc; 
            padding: 0 10px 15px 10px;
        }
    </style>
</head>
<body class="<?= e($theme) ?>">
    <header>
        <h1>Налаштування теми</h1>
        <nav>
            <a href="index.php">Головна</a>
            <a href="preferences.php">Налаштування</a>
            <a href="visits.php">Відвідування</a>
            <a href="favorites.php">Обране</a>
        </nav>
    </header>

    <br><hr>

    <main>
        <p>Поточна тема сайту: <strong><?= e($theme) ?></strong></p>

        <?php if ($error !== null): ?>
            <p style="color: #d00000;"><?= e($error) ?></p>
        <?php endif; ?>

        <div class="form-group">
            <h3>Змінити тему</h3>
            <form action="preferences.php" method="POST">
                <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
                <input type="hidden" name="action" value="save">
                
                <label>
                    <input type="radio" name="theme" value="light" 
                           <?= $theme === 'light' ? 'checked' : '' ?>>
                    Світла
                </label>
                
                <label>
                    <input type="radio" name="theme" value="dark" 
                           <?= $theme === 'dark' ? 'checked' : '' ?>>
                    Темна
                </label>

                <br><br>
    
                <button type="submit">Зберегти на 30 днів</button>
            </form>
        </div>

        <div>
            <form action="preferences.php" method="POST">
                <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
                <input type="hidden" name="action" value="reset">

                <button type="submit">Скинути тему</button>
            </form>
        </div>
    </main>
</body>
</html>
