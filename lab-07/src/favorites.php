<?php
require __DIR__ . '/bootstrap.php';

$catalog = [
    'cinema' => 'Кіновечір під зорями',
    'quiz'   => 'Інтелектуальний IT-Квіз',
    'eco'    => 'Екологічний волонтерський хакатон'
];

$isEmptyFavorites = 
    !isset($_SESSION['favorites']) || 
    !is_array($_SESSION['favorites']);

if ($isEmptyFavorites) {
    $_SESSION['favorites'] = [];
}

$error = null;

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    requireValidPost();

    $action = $_POST['action'] ?? null;

    if ($action === 'logout') {
        $_SESSION = [];

        $params = session_get_cookie_params();

        $successCookie = setcookie(
            session_name(),
            '',
            [
                'expires' => time() - 3600,
                'path' => $params['path'],
                'domain' => $params['domain'],
                'secure' => $params['secure'],
                'httponly' => $params['httponly'],
                'samesite' => $params['samesite'] ?? 'Lax',
            ]
        );

        $successDestroy = session_destroy();

        if ($successCookie && $successDestroy) {
            header('Location: index.php', true, 303);
            exit;
        } else {
            $error = 'Не вдалося коректно завершити сеанс.';
        }
    }
    elseif ($action === 'clear') {
        $_SESSION['favorites'] = [];
        $_SESSION['flash_message'] = 'Список обраного успішно очищено!';
        header('Location: favorites.php', true, 303);
        exit;
    }
    elseif (in_array($action, ['add', 'remove'], true)) {
        $id = $_POST['id'] ?? null;

        if (is_string($id) && array_key_exists($id, $catalog)) {
            $name = $catalog[$id]; 
            
            if ($action === 'add') {
                if (!in_array($id, $_SESSION['favorites'], true)) {
                    $_SESSION['favorites'][] = $id;
                    $_SESSION['flash_message'] = "Захід \"{$name}\" додано до обраного.";
                }
            } elseif ($action === 'remove') {
                $index = array_search($id, $_SESSION['favorites'], true);

                if ($index !== false) {
                    array_splice($_SESSION['favorites'], $index, 1);
                    $_SESSION['flash_message'] = "Захід \"{$name}\" видалено з обраного.";
                }
            }

            header('Location: favorites.php', true, 303);
            exit;
        } else {
            $error = 'Некоректний ідентифікатор заходу.';
        }
    } else {
        $error = 'Невідома дія форми.';
    }
}

$flashMessage = $_SESSION['flash_message'] ?? null;

if ($flashMessage !== null) {
    unset($_SESSION['flash_message']);
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
    <title>Обрані заходи</title>
    <style>
        body.light { background-color: #ffffff; color: #333333; }
        body.dark  { background-color: #222222; color: #ffffff; }
        nav a      { margin-right: 15px; }

        table { 
            border-collapse: collapse; 
            margin-top: 25px;
        }

        th, td { 
            border: 1px solid #ccc; 
            padding: 7.5px 15px; 
        }

        .section { 
            border: 1px solid #ccc; 
            padding: 5px 15px; 
            margin-top: 20px; 
        }

        .button-exit {
            background-color: #e60000; 
            color: white; 
            padding: 8px 12px;
            margin-top: 20px; 
        }

        .flash-success { 
            background-color: #eff8f1; 
            color: #107d35; 
            padding: 10px; 
            border-radius: 5px; 
            margin-top: 15px;
        }
        
    </style>
</head>

<body class="<?= e($theme) ?>">
    <header>
        <h1>Керування обраними заходами</h1>
        <nav>
            <a href="index.php">Головна</a>
            <a href="preferences.php">Налаштування</a>
            <a href="visits.php">Відвідування</a>
            <a href="favorites.php">Обране</a>
        </nav>
    </header>

    <main>
        <?php if ($error !== null): ?>
            <p style="color: #d00000;"><?= e($error) ?></p>
        <?php endif; ?>

        <?php if ($flashMessage !== null): ?>
            <div class="flash-success">
                <?= e($flashMessage) ?>
            </div>
        <?php endif; ?>

        <div>
            <table>
                <thead>
                    <tr>
                        <th>Назва заходу</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($catalog as $id => $name): ?>
                        <tr>
                            <td><?= e($name) ?></td>
                            <td>
                                <?php if (in_array($id, $_SESSION['favorites'], true)): ?>
                                    <form action="favorites.php" method="POST">
                                        <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
                                        <input type="hidden" name="action" value="remove">
                                        <input type="hidden" name="id" value="<?= e($id) ?>">

                                        <button type="submit">Видалити з обраного</button>
                                    </form>
                                <?php else: ?>
                                    <form action="favorites.php" method="POST">
                                        <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
                                        <input type="hidden" name="action" value="add">
                                        <input type="hidden" name="id" value="<?= e($id) ?>">

                                        <button type="submit">Додати до обраного</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="section">
            <h3>Ваш список обраного</h3>

            <?php if (empty($_SESSION['favorites'])): ?>
                <p>Наразі у вас немає обраних заходів.</p>
            <?php else: ?>
                <ul>
                    <?php foreach ($_SESSION['favorites'] as $favId): ?>
                        <?php if (isset($catalog[$favId])): ?>
                            <li><?= e($catalog[$favId]) ?></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>

                <form action="favorites.php" method="POST">
                    <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
                    <input type="hidden" name="action" value="clear">

                    <button type="submit">Очистити обране</button>
                </form>
            <?php endif; ?>
        </div>

        <form action="favorites.php" method="POST">
            <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
            <input type="hidden" name="action" value="logout">

            <button type="submit" class="button-exit">
                Завершити сеанс (Вихід)
            </button>
        </form>
    </main>
</body>
</html>
