<?php
declare(strict_types=1);

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function ensureJsonFileExists(string $path): void
{
    if (!is_file($path)) {
        $initialJson = json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        if (file_put_contents($path, $initialJson) === false) {
            throw new RuntimeException('Не вдалося створити початковий JSON-файл.');
        }
    }
}

function readText(string $path): string
{
    if (!is_file($path) || !is_readable($path)) {
        throw new RuntimeException('Файл відсутній або недоступний для читання.');
    }
    $text = file_get_contents($path);
    if ($text === false) {
        throw new RuntimeException('Не вдалося прочитати файл.');
    }
    if (trim($text) === '') {
        throw new RuntimeException('Файл порожній.');
    }
    return $text;
}

function parseJson(string $text): array
{
    if (trim($text) === '') {
        throw new RuntimeException('JSON порожній.');
    }
    try {
        $data = json_decode($text, true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $e) {
        throw new RuntimeException('Некоректний синтаксис JSON.');
    }
    if (!is_array($data)) {
        throw new RuntimeException('Очікується масив записів у JSON.');
    }
    return $data;
}

function validatePosts(mixed $posts): array
{
    if (!is_array($posts) || !array_is_list($posts)) {
        throw new RuntimeException('Очікується список записів.');
    }
    $validated = [];
    foreach ($posts as $post) {
        if (!is_array($post) || count($post) !== 4
            || !isset($post['id'], $post['title'], $post['content'], $post['views'])
            || !is_string($post['id']) || strlen($post['id']) !== 16
            || !is_string($post['title']) || mb_strlen($post['title'], 'UTF-8') < 5 || mb_strlen($post['title'], 'UTF-8') > 100
            || !is_string($post['content']) || mb_strlen($post['content'], 'UTF-8') < 10 || mb_strlen($post['content'], 'UTF-8') > 5000
        ) {
            throw new RuntimeException('Некоректні дані публікації в JSON.');
        }

        $rawViews = $post['views'];
        $views = false;
        if (is_int($rawViews)) {
            if ($rawViews >= 0 && $rawViews <= 1000000) {
                $views = $rawViews;
            }
        } elseif (is_string($rawViews) && ctype_digit($rawViews)) {
            $filtered = filter_var($rawViews, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 1000000]]);
            if ($filtered !== false) {
                $views = $filtered;
            }
        }

        if ($views === false) {
            throw new RuntimeException(
                'Кількість переглядів має бути цілим числом 0–1000000.'
            );
        }

        $post['views'] = $views;
        $validated[] = $post;
    }
    return $validated;
}

function postsFromJson(string $text): array
{
    $data = parseJson($text);
    return validatePosts($data);
}

function postsToJson(array $posts): string
{
    $posts = validatePosts($posts);
    return json_encode($posts, 
        JSON_PRETTY_PRINT | 
        JSON_UNESCAPED_UNICODE | 
        JSON_THROW_ON_ERROR
    );
}