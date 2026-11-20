<?php
declare(strict_types=1);

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/lab-11/',
    'domain' => '',
    'secure' => false,
    'httponly' => true,
    'samesite' => 'Strict'
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function connectDatabase(): mysqli
{
    $config = [];
    foreach (['DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASSWORD'] as $key) {
        $value = getenv($key);
        if ($value === false || ($key !== 'DB_PASSWORD' && $value === '')) {
            throw new RuntimeException('Потрібно налаштувати реквізити БД у середовищі PHP.');
        }
        $config[$key] = $value;
    }
    $port = filter_var($config['DB_PORT'], FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1, 'max_range' => 65535]]);
    if ($port === false) {
        throw new RuntimeException('Некоректний порт БД.');
    }
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $db = mysqli_init();
    if ($db === false) {
        throw new RuntimeException('Не вдалося ініціалізувати MySQLi.');
    }
    try {
        mysqli_options($db, MYSQLI_OPT_CONNECT_TIMEOUT, 5);
        mysqli_real_connect($db, $config['DB_HOST'], $config['DB_USER'], $config['DB_PASSWORD'], $config['DB_NAME'], $port);
        mysqli_set_charset($db, 'utf8mb4');
        return $db;
    } catch (Throwable $exception) {
        mysqli_close($db);
        throw $exception;
    }
}

function validateProduct(array $input): array
{
    $values = [];
    foreach (['product_code', 'product_name', 'product_desc', 'price'] as $key) {
        $raw = $input[$key] ?? null;
        if (!is_string($raw) || !mb_check_encoding($raw, 'UTF-8')) {
            throw new InvalidArgumentException('Усі поля товару мають бути рядками UTF-8.');
        }
        $values[$key] = trim($raw);
    }
    if (!preg_match('/\A[A-Z0-9-]{3,20}\z/', $values['product_code'])) {
        throw new InvalidArgumentException('Код: 3–20 великих латинських літер, цифр або дефісів.');
    }
    if ($values['product_name'] === '' || mb_strlen($values['product_name'], 'UTF-8') > 80
        || $values['product_desc'] === '' || mb_strlen($values['product_desc'], 'UTF-8') > 500) {
        throw new InvalidArgumentException('Назва: 1–80 символів, опис: 1–500 символів.');
    }
    if (!preg_match('/\A(?:0|[1-9][0-9]{0,7})(?:\.[0-9]{1,2})?\z/', $values['price'])) {
        throw new InvalidArgumentException('Ціна: 0–99999999.99, крапка, до двох дробових цифр.');
    }
    [$integer, $fraction] = array_pad(explode('.', $values['price'], 2), 2, '');
    $values['price'] = $integer . '.' . str_pad($fraction, 2, '0');
    return $values;
}

function findProducts(mysqli $db, string $code): array
{
    if ($code === '') {
        $result = mysqli_query($db,
            'SELECT id, product_code, product_name, product_desc, price, created_at FROM products ORDER BY id LIMIT 100');
        try {
            $rows = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $rows[] = $row;
            }
            return $rows;
        } finally {
            mysqli_free_result($result);
        }
    }
    $stmt = mysqli_prepare($db,
        'SELECT id, product_code, product_name, product_desc, price, created_at FROM products WHERE product_code = ?');
    try {
        mysqli_stmt_bind_param($stmt, 's', $code);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $id, $foundCode, $name, $description, $price, $createdAt);
        $rows = [];
        while (mysqli_stmt_fetch($stmt) === true) {
            $rows[] = ['id' => $id, 'product_code' => $foundCode, 'product_name' => $name,
                'product_desc' => $description, 'price' => $price, 'created_at' => $createdAt];
        }
        return $rows;
    } finally {
        mysqli_stmt_close($stmt);
    }
}

function verifyCsrfToken(?string $token): void
{
    if (!is_string($token) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'],$token)) {
        throw new InvalidArgumentException('Недійсний або відсутній CSRF-токен.');
    }
}

function validateId(mixed $rawId): int
{
    if (!is_string($rawId) && !is_int($rawId)) {
        throw new InvalidArgumentException('Некоректний ідентифікатор.');
    }
    $idStr = (string)$rawId;
    if (!preg_match('/\A[1-9][0-9]{0,9}\z/', $idStr)) {
        throw new InvalidArgumentException('ID має бути додатним цілим числом.');
    }
    return (int)$idStr;
}

function findProductById(mysqli $db, int$id): ?array
{
    $stmt = mysqli_prepare($db, 'SELECT id, product_code, product_name, product_desc, price, created_at FROM products WHERE id = ?');
    try {
        mysqli_stmt_bind_param($stmt, 'i',$id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        return $row ?: null;
    } finally {
        mysqli_stmt_close($stmt);
    }
}

function validatePost(array $input): array
{
    $values = [];
    foreach (['id', 'title', 'content', 'views'] as $key) {
        $raw = $input[$key] ?? null;
        if (!is_string($raw) || !mb_check_encoding($raw, 'UTF-8')) {
            throw new InvalidArgumentException('Усі поля мають бути рядками UTF-8.');
        }
        $values[$key] = trim($raw);
    }

    if (!preg_match('/\A[a-f0-9]{15,16}\z/i', $values['id'])) {
        throw new InvalidArgumentException('ID має складатися з 15–16 шістнадцяткових символів.');
    }
    if (mb_strlen($values['title'], 'UTF-8') < 5 || mb_strlen($values['title'], 'UTF-8') > 100) {
        throw new InvalidArgumentException('Заголовок має містити від 5 до 100 символів.');
    }
    if (mb_strlen($values['content'], 'UTF-8') < 10 || mb_strlen($values['content'], 'UTF-8') > 5000) {
        throw new InvalidArgumentException('Текст статті має містити від 10 до 5000 символів.');
    }
    if (!preg_match('/\A(?:0|[1-9][0-9]*)\z/', $values['views']) || (int)$values['views'] < 0 || (int)$values['views'] > 1000000) {
        throw new InvalidArgumentException('Кількість переглядів має бути цілим числом від 0 до 1 000 000.');
    }

    return $values;
}

function findPosts(mysqli $db, string $id): array
{
    if ($id === '') {
        $result = mysqli_query($db, 'SELECT id, title, content, views FROM blog_posts ORDER BY id LIMIT 100');
        try {
            $rows = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $rows[] = $row;
            }
            return $rows;
        } finally {
            mysqli_free_result($result);
        }
    }

    $stmt = mysqli_prepare($db, 'SELECT id, title, content, views FROM blog_posts WHERE id = ?');
    try {
        mysqli_stmt_bind_param($stmt, 's', $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $foundId, $title, $content, $views);
        $rows = [];
        while (mysqli_stmt_fetch($stmt) === true) {
            $rows[] = [
                'id' => $foundId,
                'title' => $title,
                'content' => $content,
                'views' => $views
            ];
        }
        return $rows;
    } finally {
        mysqli_stmt_close($stmt);
    }
}

function validatePostId(mixed $rawId): string
{
    if (!is_string($rawId)) {
        throw new InvalidArgumentException('Некоректний ідентифікатор статті.');
    }
    $idStr = trim($rawId);
    if (!preg_match('/\A[a-f0-9]{15,16}\z/i', $idStr)) {
        throw new InvalidArgumentException('ID статті має складатися з 15–16 символів (a-f, 0-9).');
    }
    return $idStr;
}

function findPostById(mysqli $db, string $id): ?array
{
    $stmt = mysqli_prepare($db, 'SELECT id, title, content, views FROM blog_posts WHERE id = ?');
    try {
        mysqli_stmt_bind_param($stmt, 's', $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        return $row ?: null;
    } finally {
        mysqli_stmt_close($stmt);
    }
}