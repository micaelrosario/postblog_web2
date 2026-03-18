<?php

final class DevRouter
{
    public static function dispatch(): bool
    {
        $caminho = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';

        $caminhoCompleto = __DIR__ . $caminho;
        if ($caminho !== '/' && is_file($caminhoCompleto)) {
            return false;
        }

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
        return true;
    }
}

return DevRouter::dispatch();
