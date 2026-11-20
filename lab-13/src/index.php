<?php
require_once __DIR__ . '/Post.php';

function escape($text)
{
    return htmlspecialchars((string) $text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$rows = [];
$found = null;
$error = '';
$hasSearch = array_key_exists('id', $_GET);

try {
    $pdo = require __DIR__ . '/connection.php';

    $statement = $pdo->query('SELECT id, title, content, views FROM posts ORDER BY id');
    while (($post = $statement->fetchObject(Post::class)) !== false) {
        $rows[] = $post;
    }

    if (!empty($rows)) {
        $rows[0]->rename($rows[0]->title . ' [Редаговано]');
    }

    if ($hasSearch) {
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
        ]);
        if ($id === false || $id === null) {
            $error = 'Ідентифікатор має бути додатним цілим числом.';
        } else {
            $statement = $pdo->prepare(
                'SELECT id, title, content, views FROM posts WHERE id = :id'
            );
            $statement->bindValue(':id', $id, PDO::PARAM_INT);
            $statement->execute();
            $found = $statement->fetchObject(Post::class);
        }
    }
} catch (Throwable $exception) {
    error_log((string) $exception);
    http_response_code(500);
    $error = 'Блог недоступний. Перевірте журнал PHP.';
    $rows = [];
}

require __DIR__ . '/catalog.view.php';