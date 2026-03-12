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

$urlBruta = (string)($_GET['url'] ?? '');

if (trim($urlBruta) === '') {
    $caminhoInfo = (string)($_SERVER['PATH_INFO'] ?? '');
    if ($caminhoInfo !== '') {
        $urlBruta = ltrim($caminhoInfo, '/');
    } else {
        $uriRequisicao = (string)($_SERVER['REQUEST_URI'] ?? '/');
        $caminho = (string)(parse_url($uriRequisicao, PHP_URL_PATH) ?? '/');

        $nomeScript = (string)($_SERVER['SCRIPT_NAME'] ?? '');
        $base = rtrim(str_replace('\\', '/', dirname($nomeScript)), '/');
        if ($base === '/' || $base === '.') {
            $base = '';
        }

        if ($base !== '' && str_starts_with($caminho, $base)) {
            $caminho = substr($caminho, strlen($base));
            if ($caminho === '') {
                $caminho = '/';
            }
        }

        $urlBruta = ltrim($caminho, '/');
    }
}

$urlBruta = trim($urlBruta, "/ \t\n\r\0\x0B");

// Compatibilidade: /index.php/rota
if ($urlBruta === 'index.php') {
    $urlBruta = '';
} elseif (str_starts_with($urlBruta, 'index.php/')) {
    $urlBruta = substr($urlBruta, strlen('index.php/'));
}

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
    http_response_code(404);

    if ($chaveRota === 'api' || $recurso !== '') {
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

$controlador = new $nomeClasse();

$metodo = strtolower((string)($_SERVER['REQUEST_METHOD'] ?? 'GET'));
if ($metodo === 'head') {
    $metodo = 'get';
}

if (!method_exists($controlador, $metodo)) {
    http_response_code(405);

    if ($chaveRota === 'api' || $recurso !== '') {
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

$controlador->$metodo($segmentosUrl);
