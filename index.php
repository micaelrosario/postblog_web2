<?php

declare(strict_types=1);

require_once __DIR__ . "/helpers.php";
require_once __DIR__ . "/database.php";

spl_autoload_register(static function (string $class): void {
    $file = __DIR__ . "/src/{$class}.php";
    if (is_file($file)) {
        require_once $file;
    }
});

function requestPath(): string
{
    $pathInfo = (string)($_SERVER['PATH_INFO'] ?? '');
    if ($pathInfo !== '') {
        return $pathInfo;
    }

    $uri = (string)($_SERVER['REQUEST_URI'] ?? '/');
    $path = (string)(parse_url($uri, PHP_URL_PATH) ?? '/');

    $scriptName = (string)($_SERVER['SCRIPT_NAME'] ?? '');
    if ($scriptName !== '' && str_starts_with($path, $scriptName)) {
        $path = substr($path, strlen($scriptName));
        if ($path === '') {
            $path = '/';
        }
    }

    // Compatibilidade com URLs do tipo /index.php/rota
    if (str_starts_with($path, '/index.php')) {
        $path = substr($path, strlen('/index.php'));
        if ($path === '') {
            $path = '/';
        }
    }

    return $path;
}

try {
    $db = (new Database())->conectar();
} catch (Throwable $e) {
    http_response_code(500);
    headerHtml('Erro');
    echo "<div class='alert alert-danger'>Falha ao conectar no banco de dados.</div>";
    echo "<pre class='small text-muted mb-0'>" . htmlspecialchars($e->getMessage(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "</pre>";
    footerHtml();
    exit;
}

$router = new Router();
$posts = new PostController($db);
$categories = new CategoryController($db);

$router->get('/', static fn () => $posts->index());
$router->get('/posts', static fn () => $posts->index());
$router->get('/posts/novo', static fn () => $posts->create());
$router->post('/posts', static fn () => $posts->store($_POST));

$router->get('/posts/{id:\\d+}', static fn (array $p) => $posts->show((int)$p['id']));
$router->get('/posts/{id:\\d+}/editar', static fn (array $p) => $posts->edit((int)$p['id']));
$router->post('/posts/{id:\\d+}', static fn (array $p) => $posts->update((int)$p['id'], $_POST));
$router->post('/posts/{id:\\d+}/excluir', static fn (array $p) => $posts->destroy((int)$p['id']));

$router->get('/categorias', static fn () => $categories->index());
$router->get('/categorias/nova', static fn () => $categories->create());
$router->post('/categorias', static fn () => $categories->store($_POST));

$router->get('/categorias/{id:\\d+}/editar', static fn (array $p) => $categories->edit((int)$p['id']));
$router->post('/categorias/{id:\\d+}', static fn (array $p) => $categories->update((int)$p['id'], $_POST));
$router->post('/categorias/{id:\\d+}/excluir', static fn (array $p) => $categories->destroy((int)$p['id']));

$method = (string)($_SERVER['REQUEST_METHOD'] ?? 'GET');
$path = requestPath();

$dispatched = $router->dispatch($method, $path);

if (!$dispatched['matched']) {
    http_response_code(404);
    headerHtml('404');
    echo "<div class='alert alert-warning mb-3'>Rota não encontrada.</div>";
    echo "<a class='btn btn-outline-primary' href='/posts'>Ir para posts</a>";
    footerHtml();
}
?>