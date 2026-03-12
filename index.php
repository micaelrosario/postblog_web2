<?php

// Ponto único de entrada (front controller)

require_once __DIR__ . '/views/templates.php';
require_once __DIR__ . '/config/database.php';

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

$urlBruta = trim((string)($_GET['url'] ?? ''), "/ \t\n\r\0\x0B");
$urlBruta = trim($urlBruta, '/');

$segmentosUrl = $urlBruta === '' ? [] : explode('/', $urlBruta);

// Compatibilidade: API via querystring (?resource=...)
$recurso = strtolower(trim((string)($_GET['resource'] ?? '')));
if ($recurso !== '' && $urlBruta === '') {
    $segmentosUrl = ['api', $recurso];
}

$chaveRota = strtolower((string)($segmentosUrl[0] ?? ''));

// Whitelist de rotas para evitar instanciar classes arbitrárias
$rotas = [
    '' => 'Posts',
    'home' => 'Posts',
    'post' => 'Posts',
    'posts' => 'Posts',
    'categoria' => 'Categorias',
    'categorias' => 'Categorias',
    'usuario' => 'Usuarios',
    'usuarios' => 'Usuarios',
    'comentario' => 'Comentarios',
    'comentarios' => 'Comentarios',
    'perfil' => 'Perfis',
    'perfis' => 'Perfis',
    'api' => 'Api',
];

$nomeClasse = $rotas[$chaveRota] ?? null;

if ($nomeClasse === null || !class_exists($nomeClasse)) {
    if ($chaveRota === 'api' || $recurso !== '') {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');
        jsonResponse([
            'sucesso' => false,
            'mensagem' => 'Rota não encontrada.',
        ], 404);
        exit;
    }

    http_response_code(404);

    topo('404');
    echo '<div class="alert alert-warning">Rota não encontrada.</div>';
    echo '<a class="btn btn-primary" href="' . e(baseUrl('/posts')) . '">Ir para Posts</a>';
    rodape();
    exit;
}

$controlador = new $nomeClasse();

$metodo = strtolower((string)($_SERVER['REQUEST_METHOD'] ?? 'GET'));
if ($metodo === 'head') {
    $metodo = 'get';
}

if (!method_exists($controlador, $metodo)) {
    if ($chaveRota === 'api' || $recurso !== '') {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');
        jsonResponse([
            'sucesso' => false,
            'mensagem' => 'Método HTTP não suportado.',
        ], 405);
        exit;
    }

    http_response_code(405);

    topo('405');
    echo '<div class="alert alert-warning">Método HTTP não suportado.</div>';
    rodape();
    exit;
}

$controlador->$metodo($segmentosUrl);
