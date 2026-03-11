<?php

// Router para o servidor embutido do PHP (php -S).
// Permite rotas amigáveis (/posts, /categorias, etc.) apontando para index.php.

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';

$fullPath = __DIR__ . $path;
if ($path !== '/' && is_file($fullPath)) {
    return false;
}

// Simula o rewrite do .htaccess: /posts -> index.php?url=posts
if (!isset($_GET['url'])) {
    $clean = trim((string)$path, '/');
    if ($clean === 'index.php') {
        $clean = '';
    }
    $_GET['url'] = $clean;
}

require __DIR__ . '/index.php';
