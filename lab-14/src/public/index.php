<?php
session_start();

$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'user';

require_once dirname(__DIR__) . '/app/models/User.php';
require_once dirname(__DIR__) . '/app/models/Post.php';

$pdo = require dirname(__DIR__) . '/app/config/database.php';

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$csrfToken       = $_SESSION['csrf_token'];
$currentUserRole = $_SESSION['user_role'] ?? 'user';

$currentUserId = isset($_SESSION['user_id']) 
    ? (int) $_SESSION['user_id'] 
    : null;

header('Content-Type: text/html; charset=UTF-8');

$entity = $_GET['entity'] ?? 'users';
$action = $_GET['action'] ?? 'index';
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($entity === 'posts') {
    if ($action === 'edit') {
        if ($currentUserId === null) {
            http_response_code(401);
            exit('Потрібна авторизація');
        }

        if ($id === false || $id === null || $id <= 0) {
            http_response_code(400);
            exit('Некоректний ідентифікатор');
        }

        $post = Post::find($pdo, $id);
        if ($post === null) {
            http_response_code(404);
            exit('Запис не знайдено');
        }

        if ($currentUserRole !== 'admin' && $post->user_id !== $currentUserId) {
            http_response_code(403);
            exit('Доступ заборонено: ви не можете редагувати чужий запис');
        }

        $errors = [];
        $formData = [
            'title'   => $post->title,
            'content' => $post->content,
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $postedToken = (string) ($_POST['csrf_token'] ?? '');
            if (!hash_equals($csrfToken, $postedToken)) {
                http_response_code(403);
                exit('Помилка перевірки CSRF-токена.');
            }

            $title   = is_string($_POST['title']   ?? null) ? trim($_POST['title'])   : '';
            $content = is_string($_POST['content'] ?? null) ? trim($_POST['content']) : '';

            $formData['title']   = $title;
            $formData['content'] = $content;

            if ($title === '' || mb_strlen($title) > 150) {
                $errors[] = 'Заголовок обов’язковий і не має перевищувати 150 символів';
            }
            if ($content === '') {
                $errors[] = 'Текст публікації не може бути порожнім';
            }

            if ($errors === []) {
                try {
                    $post->title = $title;
                    $post->content = $content;
                    $post->update();

                    header('Location: index.php?entity=posts&id=' . $post->id, true, 303);
                    exit;
                } catch (PDOException $ex) {
                    http_response_code(500);
                    exit('Помилка бази даних під час збереження');
                }
            }
        }

        require dirname(__DIR__) . '/app/views/posts/v_edit.php';
        exit;
    }

    if ($action === 'delete') {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Метод не дозволено');
        }

        if ($currentUserId === null) {
            http_response_code(401);
            exit('Потрібна авторизація');
        }

        $postedToken = (string) ($_POST['csrf_token'] ?? '');
        if (!hash_equals($csrfToken, $postedToken)) {
            http_response_code(403);
            exit('Помилка перевірки CSRF-токена');
        }

        if ($id === false || $id === null || $id <= 0) {
            http_response_code(400);
            exit('Некоректний ідентифікатор');
        }

        $post = Post::find($pdo, $id);
        if ($post === null) {
            http_response_code(404);
            exit('Запис не знайдено');
        }

        if ($currentUserRole !== 'admin' && $post->user_id !== $currentUserId) {
            http_response_code(403);
            exit('Доступ заборонено');
        }

        Post::delete($pdo, $post);

        header('Location: index.php?entity=posts', true, 303);
        exit;
    } else if (isset($_GET['id'])) {
        $post = ($id !== false && $id !== null && $id > 0)
            ? Post::find($pdo, $id)
            : null;

        $canEdit = $post !== null
            && $currentUserId !== null
            && ($currentUserRole === 'admin' || $post->user_id === $currentUserId);

        require dirname(__DIR__) . '/app/views/posts/v_show.php';
    } else {
        $posts = Post::all($pdo);
        require dirname(__DIR__) . '/app/views/posts/v_index.php';
    }
} else {
    if (isset($_GET['id'])) {
        $id = (int) filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        $user = User::find($pdo, $id);
        require dirname(__DIR__) . '/app/views/users/v_show.php';
    } else {
        $users = User::all($pdo);
        require dirname(__DIR__) . '/app/views/users/v_index.php';
    }
}
