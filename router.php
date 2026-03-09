<?php

// Router para o servidor embutido do PHP (php -S)
// Permite URLs amigáveis (/posts, /posts/1, etc.) como no Apache/.htaccess.

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';

// Se for um arquivo real (CSS/JS/imagens), deixa o servidor embutido servir direto.
$fullPath = __DIR__ . $path;
if ($path !== '/' && is_file($fullPath)) {
    return false;
}

require __DIR__ . '/index.php';
