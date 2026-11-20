<?php
require_once dirname(__DIR__) . '/app/core/Model.php';
$pdo = require dirname(__DIR__) . '/app/config/database.php';

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$entities = [
    'users' => [
        'table' => 'users',
        'title' => 'Користувачі',
        'columns' => [
            'username' => 'Ім’я користувача',
            'email' => 'Електронна пошта',
        ],
    ],
    'posts' => [
        'table' => 'posts',
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

$table   = $entities[$entityKey]['table'];
$title   = $entities[$entityKey]['title'];
$columns = $entities[$entityKey]['columns'];

header('Content-Type: text/html; charset=UTF-8');

if (isset($_GET['id'])) {
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    $item = $id !== false && $id !== null
        ? Model::find($pdo, $id, $table)
        : null;

    require dirname(__DIR__) . '/app/views/v_show.php';
} else {
    $items = Model::all($pdo, $table);

    require dirname(__DIR__) . '/app/views/v_index.php';
}
