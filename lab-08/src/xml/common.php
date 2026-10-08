<?php
declare(strict_types=1);

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function ensureXmlFileExists(string $path): void
{
    if (!is_file($path)) {
        $initialXml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<posts>\n</posts>";
        if (file_put_contents($path, $initialXml) === false) {
            throw new RuntimeException('Не вдалося створити початковий XML-файл.');
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

function parseXml(string $text): DOMDocument
{
    if (trim($text) === '') {
        throw new RuntimeException('XML порожній.');
    }
    $previous = libxml_use_internal_errors(true);
    libxml_clear_errors();
    try {
        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = false;
        if (!$dom->loadXML($text, LIBXML_NONET)) {
            throw new RuntimeException('Некоректний синтаксис XML.');
        }
        if ($dom->doctype !== null) {
            throw new RuntimeException('DTD у цьому навчальному форматі не дозволено.');
        }
        return $dom;
    } finally {
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
    }
}

function validatePosts(mixed $posts): array
{
    if (!is_array($posts) || !array_is_list($posts)) {
        throw new RuntimeException('Очікується список записів.');
    }
    foreach ($posts as $post) {
        if (!is_array($post) || count($post) !== 4
            || !isset($post['id'], $post['title'], $post['content'], $post['views'])
            || !is_string($post['id']) || strlen($post['id']) !== 16
            || !is_string($post['title']) || mb_strlen($post['title'], 'UTF-8') < 5 || mb_strlen($post['title'], 'UTF-8') > 100
            || !is_string($post['content']) || mb_strlen($post['content'], 'UTF-8') < 10 || mb_strlen($post['content'], 'UTF-8') > 5000
            || !is_int($post['views']) || $post['views'] < 0 || $post['views'] > 1000000
        ) {
            throw new RuntimeException('Некоректні дані публікації в XML.');
        }
    }
    return $posts;
}

function postsFromXml(DOMDocument $dom): array
{
    $root = $dom->documentElement;
    if ($root === null || $root->tagName !== 'posts' || $root->attributes->length !== 0) {
        throw new RuntimeException('Очікується кореневий елемент posts без атрибутів.');
    }
    $posts = [];
    foreach ($root->childNodes as $node) {
        if ($node instanceof DOMText && trim($node->textContent) === '') {
            continue;
        }
        if (!$node instanceof DOMElement || $node->tagName !== 'post' || $node->attributes->length !== 0) {
            throw new RuntimeException('Очікується елемент post без атрибутів.');
        }
        $fields = [];
        foreach ($node->childNodes as $field) {
            if ($field instanceof DOMText && trim($field->textContent) === '') {
                continue;
            }
            if (!$field instanceof DOMElement
                || !in_array($field->tagName, ['id', 'title', 'content', 'views'], true)
                || array_key_exists($field->tagName, $fields)
                || $field->attributes->length !== 0) {
                continue;
            }
            foreach ($field->childNodes as $part) {
                if (!$part instanceof DOMText && !$part instanceof DOMCdataSection) {
                    throw new RuntimeException('Поле має містити лише текст.');
                }
            }
            $fields[$field->tagName] = $field->textContent;
        }

        if (!isset($fields['id'], $fields['title'], $fields['content'], $fields['views'])) {
            continue;
        }

        $rawViews = $fields['views'];
        $views = ctype_digit($rawViews)
            ? filter_var($rawViews, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 1000000]])
            : false;

        if ($views === false) {
            throw new RuntimeException('Кількість переглядів має бути цілим числом 0–1000000.');
        }

        $fields['views'] = $views;
        $posts[] = $fields;
    }
    return validatePosts($posts);
}

function postsToXml(array $posts): DOMDocument
{
    $posts = validatePosts($posts);
    $dom = new DOMDocument('1.0', 'UTF-8');
    $dom->formatOutput = true;
    $root = $dom->createElement('posts');
    $dom->appendChild($root);
    foreach ($posts as $post) {
        $node = $dom->createElement('post');
        foreach ($post as $key => $value) {
            $field = $dom->createElement($key);
            $field->appendChild($dom->createTextNode((string) $value));
            $node->appendChild($field);
        }
        $root->appendChild($node);
    }
    return $dom;
}