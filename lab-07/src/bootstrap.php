<?php
const COOKIE_PATH = '/lab-07/';
const COOKIE_SECURE = false;

ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
session_name('LAB07SESSID');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => COOKIE_PATH,
    'secure' => COOKIE_SECURE,
    'httponly' => true,
    'samesite' => 'Lax',
]);
if (!session_start()) {
    http_response_code(500);
    exit('Не вдалося відкрити сесію.');
}
if (!isset($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function cookieOptions(int $expires): array
{
    return [
        'expires' => $expires,
        'path' => COOKIE_PATH,
        'secure' => COOKIE_SECURE,
        'httponly' => true,
        'samesite' => 'Lax',
    ];
}

function requireValidPost(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        header('Allow: POST');
        http_response_code(405);
        exit('Потрібен POST-запит.');
    }
    $token = $_POST['csrf'] ?? null;
    if (!is_string($token) || !hash_equals($_SESSION['csrf'], $token)) {
        http_response_code(403);
        exit('Недійсний токен форми.');
    }
}