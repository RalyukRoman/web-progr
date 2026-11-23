<?php
require_once dirname(__DIR__) . '/app/core/Model.php';
require_once dirname(__DIR__) . '/app/models/User.php';
require_once dirname(__DIR__) . '/app/models/Post.php';

$pdo = require dirname(__DIR__) . '/app/config/database.php';

header('Content-Type: text/plain; charset=UTF-8');

$userWithPosts = User::find($pdo, 1);
if ($userWithPosts !== null) {
    $posts = $userWithPosts->posts();
    echo "Користувач: {$userWithPosts->username} (клас: " . get_class($userWithPosts) . ")\n";
    echo "Кількість публікацій: " . count($posts) . "\n";
   
    foreach ($posts as $p) {
        echo " - [{$p->id}] {$p->title} (клас: " . get_class($p) . ")\n";
    }
}

$emptyUser = User::find($pdo, 3);
if ($emptyUser !== null) {
    $emptyPosts = $emptyUser->posts();
    echo "Користувач: {$emptyUser->username} -> публікацій: " . count($emptyPosts) . "\n";
}

$postWithAuthor = Post::find($pdo, 1);
if ($postWithAuthor !== null) {
    $author = $postWithAuthor->user();
    echo "Публікація: {$postWithAuthor->title}\n";
    echo "Автор: " . ($author ? "{$author->username} (клас: " . get_class($author) . ")" : "немає") . "\n";
}

$orphanPost = Post::find($pdo, 6);
if ($orphanPost !== null) {
    $author = $orphanPost->user();
    echo "Публікація: {$orphanPost->title}\n";
    echo "Автор: " . ($author === null ? "NULL (коректно повернуто null)" : $author->username) . "\n";
}

$testPost = new Post($pdo);
$testPost->user_id = 3;
$testPost->title = 'Перший пост новачка';
$testPost->content = 'Перевірка create/save + зв’язок';
$testPost->views = 1;
$testPost->save();
echo "Створено пост ID={$testPost->id}, тепер у User #3 постів: " . count($emptyUser->posts()) . "\n";

$testPost->title = 'Оновлений перший пост';
$testPost->save();
echo "Оновлено заголовок: " . Post::find($pdo, $testPost->id)?->title . "\n";

$testPost->delete();
echo "Після видалення у User #3 знову постів: " . count($emptyUser->posts()) . "\n";