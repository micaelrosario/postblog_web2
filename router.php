<?php

// Router para o servidor embutido do PHP (php -S).
// Permite rotas amigáveis (/posts, /categorias, etc.) apontando para index.php.

$caminho = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';

$caminhoCompleto = __DIR__ . $caminho;
if ($caminho !== '/' && is_file($caminhoCompleto)) {
    return false;
}

// Simula o rewrite do .htaccess: /posts -> index.php?url=posts
if (!isset($_GET['url'])) {
    $limpo = trim((string)$caminho, '/');
    if ($limpo === 'index.php') {
        $limpo = '';
    } elseif (str_starts_with($limpo, 'index.php/')) {
        $limpo = substr($limpo, strlen('index.php/'));
    }
    $_GET['url'] = $limpo;
}

require __DIR__ . '/index.php';
