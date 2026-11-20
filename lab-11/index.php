<?php
declare(strict_types=1);
require __DIR__ . '/common.php';

$db = null;
$error = null;
$status = 200;

$action = $_GET['action'] ?? 'list';

$formData = [
    'id' => '',
    'product_code' => '',
    'product_name' => '',
    'product_desc' => '',
    'price' => ''
];

try {
    $db = connectDatabase();

    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        verifyCsrfToken($_POST['csrf_token'] ?? null);
        $postAction = $_POST['post_action'] ?? '';

        if ($postAction === 'create') {
            $validated = validateProduct($_POST);
            $formData = array_merge($formData, $validated);

            $stmt = mysqli_prepare($db, 'INSERT INTO products (product_code, product_name, product_desc, price) VALUES (?, ?, ?, ?)');
            try {
                mysqli_stmt_bind_param($stmt, 'ssss', $validated['product_code'], $validated['product_name'], $validated['product_desc'], $validated['price']);
                mysqli_stmt_execute($stmt);

                header('Location: index.php?msg=created', true, 303);
                exit;
            } finally {
                mysqli_stmt_close($stmt);
            }

        } elseif ($postAction === 'update') {
            $id = validateId($_POST['id'] ?? null);
            $validated = validateProduct($_POST);
            $formData = array_merge($formData, $validated, ['id' => (string)$id]);

            if (findProductById($db, $id) === null) {
                throw new InvalidArgumentException('Запис для редагування не знайдено.');
            }

            $stmt = mysqli_prepare($db, 'UPDATE products SET product_code = ?, product_name = ?, product_desc = ?, price = ? WHERE id = ?');
            try {
                mysqli_stmt_bind_param($stmt, 'ssssi', $validated['product_code'], $validated['product_name'], $validated['product_desc'], $validated['price'], $id);
                mysqli_stmt_execute($stmt);

                header('Location: index.php?msg=updated', true, 303);
                exit;
            } finally {
                mysqli_stmt_close($stmt);
            }

        } elseif ($postAction === 'delete') {
            $id = validateId($_POST['id'] ?? null);

            if (findProductById($db, $id) === null) {
                throw new InvalidArgumentException('Запис для видалення не знайдено.');
            }

            $stmt = mysqli_prepare($db, 'DELETE FROM products WHERE id = ?');
            try {
                mysqli_stmt_bind_param($stmt, 'i', $id);
                mysqli_stmt_execute($stmt);

                header('Location: index.php?msg=deleted', true, 303);
                exit;
            } finally {
                mysqli_stmt_close($stmt);
            }
        } else {
            throw new InvalidArgumentException('Невідома дія.');
        }
    }

    if ($action === 'edit') {
        $id = validateId($_GET['id'] ?? null);
        $product = findProductById($db, $id);
        if ($product === null) {
            throw new InvalidArgumentException('Товар із таким ID не знайдено.');
        }
        $formData = [
            'id' => (string)$product['id'],
            'product_code' => $product['product_code'],
            'product_name' => $product['product_name'],
            'product_desc' => $product['product_desc'],
            'price' => $product['price']
        ];
    } elseif ($action === 'delete') {
        $id = validateId($_GET['id'] ?? null);
        $product = findProductById($db, $id);
        if ($product === null) {
            throw new InvalidArgumentException('Товар із таким ID не знайдено.');
        }
        $formData['id'] = (string)$product['id'];
        $formData['product_name'] = $product['product_name'];
    }

    $searchCode = trim((string)($_GET['code'] ?? ''));
    if ($searchCode !== '' && !preg_match('/\A[A-Z0-9-]{3,20}\z/', $searchCode)) {
        throw new InvalidArgumentException('Неприпустимий формат коду для пошуку.');
    }
    $rows = findProducts($db, $searchCode);

} catch (mysqli_sql_exception $exception) {
    $status = 400;
    if ($exception->getCode() === 1062) {
        $error = 'Помилка: Товар із таким кодом вже існує в базі даних.';
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
    <title>ЛР 11 — каталог товарів</title>
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
    <h1>Каталог товарів</h1>

    <?php if ($error !== null): ?>
        <div class="alert alert-error" role="alert"><?= e($error) ?></div>
    <?php endif; ?>

    <?php if (isset($_GET['msg']) && $error === null): ?>
        <div class="alert alert-success" role="status">
            <?php if ($_GET['msg'] === 'created'): ?>Товар успішно додано.<?php endif; ?>
            <?php if ($_GET['msg'] === 'updated'): ?>Запис товару оновлено.<?php endif; ?>
            <?php if ($_GET['msg'] === 'deleted'): ?>Товар успішно видалено.<?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if ($action === 'delete'): ?>
        <div class="form-card">
            <h2>Підтвердження видалення</h2>
            <p>Ви дійсно бажаєте видалити товар <strong><?= e($formData['product_name']) ?></strong> (ID: <?= (int)$formData['id'] ?>)?</p>
            <form action="index.php" method="post">
                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                <input type="hidden" name="post_action" value="delete">
                <input type="hidden" name="id" value="<?= e($formData['id']) ?>">
                <div class="actions">
                    <button type="submit" class="btn btn-danger">Так, видалити</button>
                    <a href="index.php" class="btn btn-secondary">Скасувати</a>
                </div>
            </form>
        </div>
    <?php else: ?>
        <div class="form-card">
            <h2><?= $action === 'edit' ? 'Редагування товару ID ' . e($formData['id']) : 'Додати новий товар' ?></h2>
            <form action="index.php" method="post">
                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                <input type="hidden" name="post_action" value="<?= $action === 'edit' ? 'update' : 'create' ?>">
                <?php if ($action === 'edit'): ?>
                    <input type="hidden" name="id" value="<?= e($formData['id']) ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label for="product_code">Код товару (3–20 символів: A–Z, 0–9, -):</label>
                    <input type="text" id="product_code" name="product_code" value="<?= e($formData['product_code']) ?>" required maxlength="20">
                </div>

                <div class="form-group">
                    <label for="product_name">Назва товару (1–80 символів):</label>
                    <input type="text" id="product_name" name="product_name" value="<?= e($formData['product_name']) ?>" required maxlength="80">
                </div>

                <div class="form-group">
                    <label for="product_desc">Опис товару (1–500 символів):</label>
                    <textarea id="product_desc" name="product_desc" rows="3" required maxlength="500"><?= e($formData['product_desc']) ?></textarea>
                </div>

                <div class="form-group">
                    <label for="price">Ціна (наприклад: 0, 25.5, 100.00):</label>
                    <input type="text" id="price" name="price" value="<?= e($formData['price']) ?>" required>
                </div>

                <div class="actions">
                    <button type="submit" class="btn btn-primary"><?= $action === 'edit' ? 'Зберегти зміни' : 'Додати товар' ?></button>
                    <?php if ($action === 'edit'): ?>
                        <a href="index.php" class="btn btn-secondary">Скасувати</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <form action="index.php" method="get" class="form-search">
        <label for="code">Пошук за кодом товару:</label>
        <input id="code" name="code" value="<?= e($searchCode) ?>" maxlength="20">
        <button type="submit" class="btn btn-secondary">Знайти</button>
        <a href="index.php">Скинути</a>
    </form>

    <?php if (!empty($rows)): ?>
        <div class="table-scroll">
            <table>
                <caption>Всього записів: <?= count($rows) ?></caption>
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Код</th>
                        <th scope="col">Назва</th>
                        <th scope="col">Опис</th>
                        <th scope="col">Ціна, грн</th>
                        <th scope="col">Створено</th>
                        <th scope="col">Дії</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td><?= (int)$row['id'] ?></td>
                        <td><?= e($row['product_code']) ?></td>
                        <td><?= e($row['product_name']) ?></td>
                        <td><?= e($row['product_desc']) ?></td>
                        <td><?= e($row['price']) ?></td>
                        <td><?= e($row['created_at']) ?></td>
                        <td>
                            <a href="index.php?action=edit&id=<?= (int)$row['id'] ?>">Редагувати</a> |
                            <a href="index.php?action=delete&id=<?= (int)$row['id'] ?>">Видалити</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p>Товарів не знайдено.</p>
    <?php endif; ?>
</body>
</html>
