<?php
declare(strict_types=1);
require __DIR__ . '/common.php';

$db = null;
$error = null;
$status = 200;

$action = $_GET['action'] ?? 'list';

$formData = [
    'id' => '',
    'title' => '',
    'content' => '',
    'views' => '0'
];

try {
    $db = connectDatabase();

    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        verifyCsrfToken($_POST['csrf_token'] ?? null);
        $postAction = $_POST['post_action'] ?? '';

        if ($postAction === 'create') {
            $validated = validatePost($_POST);
            $formData = array_merge($formData, $validated);

            $stmt = mysqli_prepare($db, 'INSERT INTO blog_posts (id, title, content, views) VALUES (?, ?, ?, ?)');
            try {
                $viewsInt = (int)$validated['views'];
                mysqli_stmt_bind_param($stmt, 'sssi', $validated['id'], $validated['title'], $validated['content'], $viewsInt);
                mysqli_stmt_execute($stmt);

                header('Location: blog.php?msg=created', true, 303);
                exit;
            } finally {
                mysqli_stmt_close($stmt);
            }

        } elseif ($postAction === 'update') {
            $id = validatePostId($_POST['id'] ?? null);
            $validated = validatePost($_POST);
            $formData = array_merge($formData, $validated, ['id' => $id]);

            if (findPostById($db, $id) === null) {
                throw new InvalidArgumentException('Статтю для редагування не знайдено.');
            }

            $stmt = mysqli_prepare($db, 'UPDATE blog_posts SET title = ?, content = ?, views = ? WHERE id = ?');
            try {
                $viewsInt = (int)$validated['views'];
                mysqli_stmt_bind_param($stmt, 'ssis', $validated['title'], $validated['content'], $viewsInt, $id);
                mysqli_stmt_execute($stmt);

                header('Location: blog.php?msg=updated', true, 303);
                exit;
            } finally {
                mysqli_stmt_close($stmt);
            }

        } elseif ($postAction === 'delete') {
            $id = validatePostId($_POST['id'] ?? null);

            if (findPostById($db, $id) === null) {
                throw new InvalidArgumentException('Статтю для видалення не знайдено.');
            }

            $stmt = mysqli_prepare($db, 'DELETE FROM blog_posts WHERE id = ?');
            try {
                mysqli_stmt_bind_param($stmt, 's', $id);
                mysqli_stmt_execute($stmt);

                header('Location: blog.php?msg=deleted', true, 303);
                exit;
            } finally {
                mysqli_stmt_close($stmt);
            }
        } else {
            throw new InvalidArgumentException('Невідома дія.');
        }
    }

    if ($action === 'edit') {
        $id = validatePostId($_GET['id'] ?? null);
        $post = findPostById($db, $id);
        if ($post === null) {
            throw new InvalidArgumentException('Статтю з таким ID не знайдено.');
        }
        $formData = [
            'id' => $post['id'],
            'title' => $post['title'],
            'content' => $post['content'],
            'views' => (string)$post['views']
        ];
    } elseif ($action === 'delete') {
        $id = validatePostId($_GET['id'] ?? null);
        $post = findPostById($db, $id);
        if ($post === null) {
            throw new InvalidArgumentException('Статтю з таким ID не знайдено.');
        }
        $formData['id'] = $post['id'];
        $formData['title'] = $post['title'];
    }

    $searchId = trim((string)($_GET['id'] ?? ''));
    if ($searchId !== '' && !preg_match('/\A[a-f0-9]{15,16}\z/i', $searchId)) {
        throw new InvalidArgumentException('Неприпустимий формат ID для пошуку.');
    }
    $rows = findPosts($db, $searchId);

} catch (mysqli_sql_exception $exception) {
    $status = 400;
    if ($exception->getCode() === 1062) {
        $error = 'Помилка: Стаття з таким ID вже існує в базі даних.';
    } else {
        $status = 500;
        error_log('Database error: ' . $exception->getMessage());
        $error = 'Сталася помилка БД. Зверніться до адміністратора.';
    }
} catch (InvalidArgumentException $exception) {
    $status = 400;
    $error = $exception->getMessage();
} catch (Throwable $exception) {
    $status = 500;
    error_log('Lab11 error: ' . $exception->getMessage());
    $error = 'Виникла внутрішня помилка сервера.';
} finally {
    if ($db !== null) {
        mysqli_close($db);
    }
}

http_response_code($status);
header('Content-Type: text/html; charset=UTF-8');
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ЛР 11 — Власний блог</title>
    <style>
        body   { 
            max-width: 1100px; 
            margin: 2rem auto; 
            padding: 0 1rem; 
            font-family: system-ui, sans-serif; 
            line-height: 1.5; 
        }

        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 1rem; 
        }

        th, td { 
            padding: .5rem; 
            border: 1px solid #94a3b8; 
            text-align: left; 
        }

        th { background: #e2e8f0; }

        .alert {
            padding: .75rem; 
            border-radius: 4px; 
            margin-bottom: 1rem;
        }

        .alert-error   { border: 1px solid #ef4444; color: #991b1b; }
        .alert-success { border: 1px solid #22c55e; color: #166534; }

        .form-search { display: flex; gap: 1rem; }
        .form-group  { margin-bottom: 1rem; }
        .form-card   { margin-bottom: 2rem; }

        .form-group label { 
            display: block; 
            font-weight: bold; 
            margin-bottom: .25rem; 
        }

        .form-group input, .form-group textarea { 
            width: 100%; 
            max-width: 500px; 
            padding: .5rem; 
            border: 1px solid #6c6c6c; 
            border-radius: 4px; 
        }

        .actions { 
            margin-top: 1rem; 
            display: flex; 
            gap: .5rem; 
            align-items: center; 
        }

        .btn { 
            padding: .3rem 1rem; 
            border: none; 
            border-radius: 4px; 
        }

        .btn-primary   { background: #154ec9; color: #fff; }
        .btn-danger    { background: #d62020; color: #fff; }
        .btn-secondary { background: #64748b; color: #fff; }
    </style>
</head>
<body>
    <h1>Управління блогом</h1>

    <?php if ($error !== null): ?>
        <div class="alert alert-error" role="alert"><?= e($error) ?></div>
    <?php endif; ?>

    <?php if (isset($_GET['msg']) && $error === null): ?>
        <div class="alert alert-success" role="status">
            <?php if ($_GET['msg'] === 'created'): ?>Статтю успішно додано.<?php endif; ?>
            <?php if ($_GET['msg'] === 'updated'): ?>Статтю успішно оновлено.<?php endif; ?>
            <?php if ($_GET['msg'] === 'deleted'): ?>Статтю успішно видалено.<?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if ($action === 'delete'): ?>
        <div class="form-card">
            <h2>Підтвердження видалення</h2>
            <p>Ви дійсно бажаєте видалити статтю <strong><?= e($formData['title']) ?></strong> (ID: <?= e($formData['id']) ?>)?</p>
            <form action="blog.php" method="post">
                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                <input type="hidden" name="post_action" value="delete">
                <input type="hidden" name="id" value="<?= e($formData['id']) ?>">
                <div class="actions">
                    <button type="submit" class="btn btn-danger">Так, видалити</button>
                    <a href="blog.php" class="btn btn-secondary">Скасувати</a>
                </div>
            </form>
        </div>
    <?php else: ?>
        <div class="form-card">
            <h2><?= $action === 'edit' ? 'Редагування статті ID ' . e($formData['id']) : 'Додати нову статтю' ?></h2>
            <form action="blog.php" method="post">
                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                <input type="hidden" name="post_action" value="<?= $action === 'edit' ? 'update' : 'create' ?>">

                <div class="form-group">
                    <label for="id">Ідентифікатор (id) (15–16 символів):</label>
                    <input type="text" id="id" name="id" value="<?= e($formData['id']) ?>" required maxlength="16" <?= $action === 'edit' ? 'readonly' : '' ?>>
                </div>

                <div class="form-group">
                    <label for="title">Заголовок статті (5–100 символів):</label>
                    <input type="text" id="title" name="title" value="<?= e($formData['title']) ?>" required maxlength="100">
                </div>

                <div class="form-group">
                    <label for="content">Текст статті (10–5000 символів):</label>
                    <textarea id="content" name="content" rows="5" required maxlength="5000"><?= e($formData['content']) ?></textarea>
                </div>

                <div class="form-group">
                    <label for="views">Кількість переглядів (0–1000000):</label>
                    <input type="number" id="views" name="views" value="<?= e($formData['views']) ?>" min="0" max="1000000" required>
                </div>

                <div class="actions">
                    <button type="submit" class="btn btn-primary"><?= $action === 'edit' ? 'Зберегти зміни' : 'Додати статтю' ?></button>
                    <?php if ($action === 'edit'): ?>
                        <a href="blog.php" class="btn btn-secondary">Скасувати</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <form action="blog.php" method="get" class="form-search">
        <label for="search_id">Пошук за ID статті:</label>
        <input id="search_id" name="id" value="<?= e($searchId) ?>" maxlength="16">
        <button type="submit" class="btn btn-secondary">Знайти</button>
        <a href="blog.php">Скинути</a>
    </form>

    <?php if (!empty($rows)): ?>
        <div class="table-scroll">
            <table>
                <caption>Всього записів: <?= count($rows) ?></caption>
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Заголовок статті</th>
                        <th scope="col">Текст статті</th>
                        <th scope="col">Перегляди</th>
                        <th scope="col">Дії</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td><?= e($row['id']) ?></td>
                        <td><?= e($row['title']) ?></td>
                        <td><?= e($row['content']) ?></td>
                        <td><?= (int)$row['views'] ?></td>
                        <td>
                            <a href="blog.php?action=edit&id=<?= e($row['id']) ?>">Редагувати</a> |
                            <a href="blog.php?action=delete&id=<?= e($row['id']) ?>">Видалити</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p>Статей не знайдено.</p>
    <?php endif; ?>
</body>
</html>