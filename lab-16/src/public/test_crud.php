<?php
require_once dirname(__DIR__) . '/app/core/Model.php';
require_once dirname(__DIR__) . '/app/models/User.php';
require_once dirname(__DIR__) . '/app/models/Post.php';

$pdo = require dirname(__DIR__) . '/app/config/database.php';

// Перевірка класу при вибірці
$posts = Post::all($pdo);
var_dump(get_class($posts[0]));

$user = User::find($pdo, 1);
var_dump(get_class($user));

// Створення нового об'єкта та збереження через save()
$newPost = new Post($pdo);
$newPost->user_id = 1;
$newPost->title = 'Тестова публікація';
$newPost->content = 'Перевірка збереження об’єкта';
$newPost->views = 0;
$newPost->save();

echo "Створено пост з ID: " . $newPost->id . ", клас: " . get_class($newPost);

// Оновлення наявного об'єкта через save()
$foundPost = Post::find($pdo, $newPost->id);
if ($foundPost !== null) {
    $foundPost->title = 'Оновлений заголовок';
    $foundPost->views = 15;
    $foundPost->save();
}

// Видалення тестового запису
if ($foundPost !== null) {
    $foundPost->delete();
}