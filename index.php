<?php

declare(strict_types=1);

// Ponto único de entrada (front controller)
if (!defined('ACCESS')) {
    define('ACCESS', true);
}

require_once __DIR__ . '/views/templates.php';
require_once __DIR__ . '/config/database.php';

require_once __DIR__ . '/traits/trait_Template.php';
require_once __DIR__ . '/traits/trait_Response.php';
require_once __DIR__ . '/traits/trait_Seguranca.php';

require_once __DIR__ . '/src/models/Post.php';
require_once __DIR__ . '/src/models/Categoria.php';
require_once __DIR__ . '/src/models/Usuario.php';
require_once __DIR__ . '/src/models/Comentario.php';
require_once __DIR__ . '/src/models/PerfilAutor.php';

require_once __DIR__ . '/classes/Posts.php';
require_once __DIR__ . '/classes/Categorias.php';
require_once __DIR__ . '/classes/Usuarios.php';
require_once __DIR__ . '/classes/Comentarios.php';
require_once __DIR__ . '/classes/Perfis.php';
require_once __DIR__ . '/classes/Api.php';

$urlRaw = trim((string)($_GET['url'] ?? ''), "/ \t\n\r\0\x0B");
$url = $urlRaw === '' ? [] : explode('/', $urlRaw);

// Compatibilidade: API via querystring (?resource=...)
$resource = strtolower(trim((string)($_GET['resource'] ?? '')));
if ($resource !== '' && $urlRaw === '') {
    $url = ['api', $resource];
}

$routeKey = strtolower((string)($url[0] ?? ''));

// Whitelist de rotas para evitar instanciar classes arbitrárias
$routes = [
    '' => 'Posts',
    'home' => 'Posts',
    'posts' => 'Posts',
    'categorias' => 'Categorias',
    'usuarios' => 'Usuarios',
    'comentarios' => 'Comentarios',
    'perfis' => 'Perfis',
    'api' => 'Api',
];

$className = $routes[$routeKey] ?? null;

if ($className === null || !class_exists($className)) {
    http_response_code(404);

    if ($routeKey === 'api' || $resource !== '') {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Rota não encontrada.',
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    topo('404');
    echo '<div class="alert alert-warning">Rota não encontrada.</div>';
    echo '<a class="btn btn-primary" href="' . e(baseUrl('/posts')) . '">Ir para Posts</a>';
    rodape();
    exit;
}

$controller = new $className();

$method = strtolower((string)($_SERVER['REQUEST_METHOD'] ?? 'GET'));
if ($method === 'head') {
    $method = 'get';
}

if (!method_exists($controller, $method)) {
    http_response_code(405);

    if ($routeKey === 'api' || $resource !== '') {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Método HTTP não suportado.',
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    topo('405');
    echo '<div class="alert alert-warning">Método HTTP não suportado.</div>';
    rodape();
    exit;
}

$controller->$method($url);
