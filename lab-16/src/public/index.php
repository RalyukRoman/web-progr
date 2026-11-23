<?php
require_once dirname(__DIR__) . '/app/core/Model.php';
require_once dirname(__DIR__) . '/app/models/User.php';
require_once dirname(__DIR__) . '/app/models/Post.php';

$pdo = require dirname(__DIR__) . '/app/config/database.php';

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$entities = [
    'users' => [
        'model' => User::class,
        'title' => 'Користувачі',
        'columns' => [
            'username' => 'Ім’я користувача',
            'email' => 'Електронна пошта',
        ],
    ],
    'posts' => [
        'model' => Post::class,
        'title' => 'Публікації',
        'columns' => [
            'title' => 'Заголовок',
            'content' => 'Текст публікації',
            'user_id' => 'ID автора',
            'views' => 'Перегляди',
        ],
    ],
];

$entityKey = $_GET['entity'] ?? 'users';
if (!isset($entities[$entityKey])) {
    $entityKey = 'users';
}

$modelClass = $entities[$entityKey]['model'];
$title      = $entities[$entityKey]['title'];
$columns    = $entities[$entityKey]['columns'];

header('Content-Type: text/html; charset=UTF-8');

if (isset($_GET['id'])) {
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    $item = ($id !== false && $id !== null)
        ? $modelClass::find($pdo, $id)
        : null;

    require dirname(__DIR__) . '/app/views/v_show.php';
} else {
    $items = $modelClass::all($pdo);

    require dirname(__DIR__) . '/app/views/v_index.php';
}