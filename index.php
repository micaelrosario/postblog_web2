<?php

// Front controller: centraliza rotas HTML e API em um único ponto.

require_once __DIR__ . '/views/templates.php';
require_once __DIR__ . '/config/database.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/src/models/Post.php';
require_once __DIR__ . '/src/models/Categoria.php';
require_once __DIR__ . '/src/models/Usuario.php';
require_once __DIR__ . '/src/models/Comentario.php';
require_once __DIR__ . '/src/models/PerfilAutor.php';

require_once __DIR__ . '/classes/Posts.php';
require_once __DIR__ . '/classes/AdicionarPosts.php';
require_once __DIR__ . '/classes/Categorias.php';
require_once __DIR__ . '/classes/Usuarios.php';
require_once __DIR__ . '/classes/Comentarios.php';
require_once __DIR__ . '/classes/Perfis.php';
require_once __DIR__ . '/classes/Api.php';
require_once __DIR__ . '/classes/Auth.php';

// Rota amigável via rewrite em ?url=...
$urlBruta = trim((string)($_GET['url'] ?? ''), "/ \t\n\r\0\x0B");
$urlBruta = trim($urlBruta, '/');

// Fallback: quando não existe rewrite preenchendo ?url=..., derivar a rota da URL.
// Isso evita loop de redirects ao rodar com `php -S ... index.php` ou setups sem rewrite.
if ($urlBruta === '') {
    $caminhoRequest = (string)(parse_url((string)($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH) ?? '/');
    $caminhoRequest = trim($caminhoRequest, '/');

    $nomeScript = (string)($_SERVER['SCRIPT_NAME'] ?? '');
    $base = rtrim(str_replace('\\', '/', dirname($nomeScript)), '/');
    $base = trim($base, '/');

    if ($base !== '' && str_starts_with($caminhoRequest, $base . '/')) {
        $caminhoRequest = substr($caminhoRequest, strlen($base) + 1);
    } elseif ($base !== '' && $caminhoRequest === $base) {
        $caminhoRequest = '';
    }

    if ($caminhoRequest === 'index.php') {
        $caminhoRequest = '';
    } elseif (str_starts_with($caminhoRequest, 'index.php/')) {
        $caminhoRequest = substr($caminhoRequest, strlen('index.php/'));
    }

    if ($caminhoRequest !== '') {
        $urlBruta = $caminhoRequest;
    }
}

// Segmentos da rota (ex.: /api/posts/1)
$segmentosUrl = $urlBruta === '' ? [] : explode('/', $urlBruta);

// Compatibilidade: quando a URL chega como /api/index.php/... (sem arquivo físico)
if (strtolower((string)($segmentosUrl[0] ?? '')) === 'api' && strtolower((string)($segmentosUrl[1] ?? '')) === 'index.php') {
    array_splice($segmentosUrl, 1, 1);
}

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
    'inicio' => 'Posts',
    'post' => 'Posts',
    'posts' => 'Posts',
    'adicionar-posts' => 'AdicionarPosts',
    'categoria' => 'Categorias',
    'categorias' => 'Categorias',
    'usuario' => 'Usuarios',
    'usuarios' => 'Usuarios',
    'comentario' => 'Comentarios',
    'comentarios' => 'Comentarios',
    'perfil' => 'Perfis',
    'perfis' => 'Perfis',
    'api' => 'Api',
    'login' => 'Auth',
    'cadastro' => 'Auth',
    'logout' => 'Auth',
];

$nomeClasse = $rotas[$chaveRota] ?? null;

// Rota inválida: responde JSON na API ou 404 na interface.
if ($nomeClasse === null || !class_exists($nomeClasse)) {
    if ($chaveRota === 'api' || $recurso !== '') {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');
        Http::jsonResponse([
            'sucesso' => false,
            'mensagem' => 'Rota não encontrada.',
        ], 404);
        exit;
    }

    http_response_code(404);

    Layout::topo('404');
    echo '<div class="alert alert-warning">Rota não encontrada.</div>';
    echo '<a class="btn btn-primary" href="' . Http::e(Http::baseUrl('/inicio')) . '">Ir para Início</a>';
    Layout::rodape();
    exit;
}

$usuarioAutenticado = !empty($_SESSION['usuario_id']);
$metodoRequisicao = strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET'));

// - Rotas de leitura (GET/HEAD) ficam públicas.
// - Painel/admin e ações de escrita exigem login.
$rotasPublicasHtml = ['login', 'cadastro'];
$rotasPublicasSomenteLeitura = ['', 'home', 'inicio', 'posts', 'post'];

$rotaPublicaHtml = in_array($chaveRota, $rotasPublicasHtml, true);
$rotaPublicaSomenteLeitura = in_array($chaveRota, $rotasPublicasSomenteLeitura, true);
$metodoSomenteLeitura = in_array($metodoRequisicao, ['GET', 'HEAD'], true);

if (!$usuarioAutenticado && $chaveRota !== 'api') {
    $permitirSemLogin = $rotaPublicaHtml || ($rotaPublicaSomenteLeitura && $metodoSomenteLeitura);

    if (!$permitirSemLogin) {
        if ($metodoRequisicao === 'PUT' || $metodoRequisicao === 'DELETE') {
            Http::jsonResponse([
                'sucesso' => false,
                'mensagem' => 'Não autenticado.',
            ], 401);
            exit;
        }

        Http::setFlash('Faça login para continuar.', 'danger');
        Http::redirect(Http::baseUrl('/login'), 303);
        exit;
    }
}

// Proteção simples de CSRF para rotas HTML (sessão/cookies).
// - Token gerado por sessão (meta + campo hidden nos forms)
// - Validação em POST/PUT/DELETE
// - Token pode vir via header X-CSRF-Token (necessário em DELETE via fetch)
if ($chaveRota !== 'api' && in_array($metodoRequisicao, ['POST', 'PUT', 'DELETE'], true)) {
    $tokenCsrf = trim((string)($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ''));

    // Para POST tradicional, permite enviar no corpo como campo hidden.
    if ($tokenCsrf === '' && $metodoRequisicao === 'POST') {
        $tokenCsrf = trim((string)($_POST['_csrf'] ?? ''));
    }

    if (!Http::csrfValido($tokenCsrf)) {
        if (in_array($metodoRequisicao, ['PUT', 'DELETE'], true)) {
            Http::jsonResponse([
                'sucesso' => false,
                'mensagem' => 'Requisição inválida (CSRF). Atualize a página e tente novamente.',
            ], 403);
            exit;
        }

        http_response_code(403);
        Layout::topo('403');
        echo '<div class="alert alert-danger">Requisição inválida (CSRF). Atualize a página e tente novamente.</div>';
        Layout::rodape();
        exit;
    }
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
        Http::jsonResponse([
            'sucesso' => false,
            'mensagem' => 'Método HTTP não suportado.',
        ], 405);
        exit;
    }

    http_response_code(405);

    Layout::topo('405');
    echo '<div class="alert alert-warning">Método HTTP não suportado.</div>';
    Layout::rodape();
    exit;
}

$controlador->$metodo($segmentosUrl);
