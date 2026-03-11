<?php

declare(strict_types=1);

defined('ACCESS') or die('Acesso negado');

function baseUrl(string $path = ''): string
{
    $scriptName = (string)($_SERVER['SCRIPT_NAME'] ?? '');
    $base = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');

    if ($base === '/' || $base === '.') {
        $base = '';
    }

    if ($path === '' || $path === '/') {
        return $base . '/';
    }

    if ($path[0] !== '/') {
        $path = '/' . $path;
    }

    return $base . $path;
}

function e(mixed $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function topo(string $titulo): void
{
    ?>
    <!doctype html>
    <html lang="pt-br">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?php echo e($titulo); ?></title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
            <div class="container">
                <a class="navbar-brand" href="<?php echo e(baseUrl('/posts')); ?>">BlogPost</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav" aria-controls="nav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="nav">
                    <div class="navbar-nav">
                        <a class="nav-link" href="<?php echo e(baseUrl('/posts')); ?>">Posts</a>
                        <a class="nav-link" href="<?php echo e(baseUrl('/categorias')); ?>">Categorias</a>
                        <a class="nav-link" href="<?php echo e(baseUrl('/usuarios')); ?>">Usuários</a>
                        <a class="nav-link" href="<?php echo e(baseUrl('/comentarios')); ?>">Comentários</a>
                        <a class="nav-link" href="<?php echo e(baseUrl('/perfis')); ?>">Perfis</a>
                    </div>
                </div>
            </div>
        </nav>

        <main class="container">
    <?php
}

function rodape(): void
{
    ?>
        </main>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>
    <?php
}
