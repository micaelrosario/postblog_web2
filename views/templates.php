<?php

final class Http
{
    public static function baseUrl(string $caminho = ''): string
    {
        $nomeScript = (string)($_SERVER['SCRIPT_NAME'] ?? '');
        $base = rtrim(str_replace('\\', '/', dirname($nomeScript)), '/');

        if ($base === '/' || $base === '.') {
            $base = '';
        }

        if ($caminho === '' || $caminho === '/') {
            return $base . '/';
        }

        if ($caminho[0] !== '/') {
            $caminho = '/' . $caminho;
        }

        return $base . $caminho;
    }

    #CAMADAS DE DEFESA CONTRA XSS: ESCAPAR SAÍDA, LIMPAR ENTRADA, USAR HTTP-ONLY NOS COOKIES
    public static function e($value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    #CAMADAS DE DEFESA CONTRA INJEÇÃO DE CÓDIGOS: USAR PREPARED STATEMENTS, VALIDAR E LIMPAR ENTRADA, RESTRINGIR PERMISSÕES DO BANCO
    public static function limparArray(array $dados, array $opcoes = []): array
    {
        $naoTrim = (array)($opcoes['naoTrim'] ?? []);
        $normalizarQuebraLinha = (bool)($opcoes['normalizarQuebraLinha'] ?? true);

        $limpo = [];
        foreach ($dados as $chave => $valor) {
            if (is_array($valor)) {
                $limpo[$chave] = self::limparArray($valor, $opcoes);
                continue;
            }

            if (!is_string($valor)) {
                $limpo[$chave] = $valor;
                continue;
            }

            $v = str_replace("\0", '', $valor);

            if ($normalizarQuebraLinha) {
                $v = str_replace(["\r\n", "\r"], "\n", $v);
            }

            if (!in_array((string)$chave, $naoTrim, true)) {
                $v = trim($v);
            }

            $limpo[$chave] = $v;
        }

        return $limpo;
    }

    public static function jsonResponse($dados, int $codigoStatus = 200): void
    {
        http_response_code($codigoStatus);
        header('Content-Type: application/json; charset=UTF-8');

        echo json_encode($dados, JSON_UNESCAPED_UNICODE);
    }

    public static function lerDadosCorpo(): array
    {
        $corpoBruto = file_get_contents('php://input');
        if ($corpoBruto === false || $corpoBruto === '') {
            return [];
        }

        $tipoConteudo = strtolower((string)($_SERVER['CONTENT_TYPE'] ?? ''));

        if (str_contains($tipoConteudo, 'application/json')) {
            $decodificado = json_decode($corpoBruto, true);
            return is_array($decodificado) ? $decodificado : [];
        }

        $dados = [];
        parse_str($corpoBruto, $dados);

        return is_array($dados) ? $dados : [];
    }

    public static function lerDadosCorpoLimpo(array $opcoes = []): array
    {
        $dados = self::lerDadosCorpo();
        return self::limparArray($dados, $opcoes);
    }

    public static function redirect(string $url, int $codigo = 303): void
    {
        header('Location: ' . $url, true, $codigo);
    }

    private static function iniciarSessaoSePossivel(): bool
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return true;
        }

        if (headers_sent()) {
            return false;
        }

        @session_start();
        return session_status() === PHP_SESSION_ACTIVE;
    }

    public static function setFlash(string $mensagem, string $tipo = 'success'): void
    {
        $mensagem = trim($mensagem);
        if ($mensagem === '') {
            return;
        }

        if (!self::iniciarSessaoSePossivel()) {
            return;
        }

        $tiposPermitidos = ['success', 'danger', 'warning', 'info', 'primary', 'secondary', 'light', 'dark'];
        if (!in_array($tipo, $tiposPermitidos, true)) {
            $tipo = 'info';
        }

        $_SESSION['_flash'] = [
            'tipo' => $tipo,
            'mensagem' => $mensagem,
        ];
    }

    public static function popFlash(): ?array
    {
        if (!self::iniciarSessaoSePossivel()) {
            return null;
        }

        $flash = $_SESSION['_flash'] ?? null;
        unset($_SESSION['_flash']);

        if (!is_array($flash)) {
            return null;
        }

        $mensagem = trim((string)($flash['mensagem'] ?? ''));
        if ($mensagem === '') {
            return null;
        }

        $tipo = (string)($flash['tipo'] ?? 'info');
        if ($tipo === '') {
            $tipo = 'info';
        }

        return [
            'tipo' => $tipo,
            'mensagem' => $mensagem,
        ];
    }

    public static function csrfToken(): string
    {
        if (!self::iniciarSessaoSePossivel()) {
            return '';
        }

        $token = (string)($_SESSION['_csrf_token'] ?? '');
        if ($token !== '') {
            return $token;
        }

        try {
            $token = bin2hex(random_bytes(32));
        } catch (Throwable $e) {
            $token = bin2hex((string)microtime(true) . (string)mt_rand());
        }

        $_SESSION['_csrf_token'] = $token;
        return $token;
    }

    public static function csrfField(string $nomeCampo = '_csrf'): string
    {
        $nomeCampo = trim($nomeCampo);
        if ($nomeCampo === '') {
            return '';
        }

        $token = self::csrfToken();
        if ($token === '') {
            return '';
        }

        return '<input type="hidden" name="' . self::e($nomeCampo) . '" value="' . self::e($token) . '">';
    }

    public static function csrfValido($token): bool
    {
        if (!self::iniciarSessaoSePossivel()) {
            return false;
        }

        $enviado = trim((string)$token);
        if ($enviado === '') {
            return false;
        }

        $esperado = (string)($_SESSION['_csrf_token'] ?? '');
        if ($esperado === '') {
            return false;
        }

        return hash_equals($esperado, $enviado);
    }
}

final class Layout
{
    public static function topo(string $titulo, bool $mostrarNavbar = true): void
    {
        ?>
        <!doctype html>
        <html lang="pt-br">
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <meta name="csrf-token" content="<?php echo Http::e(Http::csrfToken()); ?>">
            <title><?php echo Http::e($titulo); ?></title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        </head>
        <body class="bg-light d-flex flex-column min-vh-100">
            <?php if ($mostrarNavbar) { ?>
                <nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top mb-4" style="background-color: #021156;">
                    <div class="container">
                        <a class="navbar-brand" href="<?php echo Http::e(Http::baseUrl('/inicio')); ?>">Filmmakers' Blog</a>
                        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav" aria-controls="nav" aria-expanded="false" aria-label="Toggle navigation">
                            <span class="navbar-toggler-icon"></span>
                        </button>
                        <div class="collapse navbar-collapse" id="nav">
                            <div class="navbar-nav">
                                <?php if (!empty($_SESSION['usuario_id'])) { ?>
                                    <a class="nav-link" href="<?php echo Http::e(Http::baseUrl('/adicionar-posts')); ?>">Posts</a>
                                    <a class="nav-link" href="<?php echo Http::e(Http::baseUrl('/categorias')); ?>">Categorias</a>
                                    <a class="nav-link" href="<?php echo Http::e(Http::baseUrl('/usuarios')); ?>">Usuários</a>
                                    <a class="nav-link" href="<?php echo Http::e(Http::baseUrl('/comentarios')); ?>">Comentários</a>
                                    <a class="nav-link" href="<?php echo Http::e(Http::baseUrl('/perfis')); ?>">Perfis</a>
                                <?php } ?>
                            </div>

                            <div class="navbar-nav ms-auto">
                                <?php if (!empty($_SESSION['usuario_id'])) { ?>
                                    <a class="nav-link" href="<?php echo Http::e(Http::baseUrl('/logout')); ?>">Sair</a>
                                <?php } else { ?>
                                    <a class="nav-link" href="<?php echo Http::e(Http::baseUrl('/login')); ?>">Entrar</a>
                                    <a class="nav-link" href="<?php echo Http::e(Http::baseUrl('/cadastro')); ?>">Cadastrar</a>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </nav>
            <?php } ?>

            <main class="container flex-grow-1 pb-5">
                <div id="app-flash">
                    <?php
                    $flash = Http::popFlash();
                    if (is_array($flash)) {
                        $tipo = (string)($flash['tipo'] ?? 'info');
                        $mensagem = (string)($flash['mensagem'] ?? '');
                        if ($mensagem !== '') {
                            echo '<div class="alert alert-' . Http::e($tipo) . ' mb-3" role="alert">' . Http::e($mensagem) . '</div>';
                        }
                    }
                    ?>
                </div>
        <?php
    }

    public static function rodape(): void
    {
        ?>
            </main>

            <footer class="mt-auto bg-dark py-2">
                <div class="container text-center small text-white-50">
                    &copy; <?php echo (int)date('Y'); ?> BlogPost
                </div>
            </footer>

            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

            <script>
                (function () {
                    const FLASH_KEY = 'postblog_flash';

                    function obterCsrfToken() {
                        const meta = document.querySelector('meta[name="csrf-token"]');
                        return meta ? String(meta.getAttribute('content') || '') : '';
                    }

                    function salvarFlash(tipo, mensagem) {
                        try {
                            sessionStorage.setItem(FLASH_KEY, JSON.stringify({
                                tipo: String(tipo || 'info'),
                                mensagem: String(mensagem || '')
                            }));
                        } catch (e) {
                            // ignore
                        }
                    }

                    function lerFlash() {
                        try {
                            const raw = sessionStorage.getItem(FLASH_KEY);
                            if (!raw) {
                                return null;
                            }

                            sessionStorage.removeItem(FLASH_KEY);

                            const parsed = JSON.parse(raw);
                            if (!parsed || typeof parsed !== 'object') {
                                return null;
                            }

                            const tipo = String(parsed.tipo || 'info');
                            const mensagem = String(parsed.mensagem || '');
                            if (!mensagem) {
                                return null;
                            }

                            return { tipo, mensagem };
                        } catch (e) {
                            try {
                                sessionStorage.removeItem(FLASH_KEY);
                            } catch (e2) {
                                // ignore
                            }
                            return null;
                        }
                    }

                    function renderizarFlash(tipo, mensagem) {
                        const container = document.getElementById('app-flash');
                        if (!container) {
                            return;
                        }

                        const permitidos = new Set(['success', 'danger', 'warning', 'info', 'primary', 'secondary', 'light', 'dark']);
                        if (!permitidos.has(tipo)) {
                            tipo = 'info';
                        }

                        const el = document.createElement('div');
                        el.className = 'alert alert-' + tipo + ' mb-3';
                        el.setAttribute('role', 'alert');
                        el.textContent = mensagem;
                        container.appendChild(el);
                    }

                    const flash = lerFlash();
                    if (flash) {
                        renderizarFlash(flash.tipo, flash.mensagem);
                    }

                    document.addEventListener('submit', async function (evento) {
                        const formulario = evento.target;
                        if (!(formulario instanceof HTMLFormElement)) {
                            return;
                        }

                        if (evento.defaultPrevented) {
                            return;
                        }

                        const metodoRest = String(formulario.dataset.metodoRest || '').trim().toUpperCase();
                        if (!metodoRest) {
                            return;
                        }

                        evento.preventDefault();

                        const redirecionar = String(formulario.dataset.redirecionar || '').trim();

                        let corpo = undefined;
                        let headers = undefined;

                        if (metodoRest !== 'DELETE') {
                            const formData = new FormData(formulario);
                            corpo = new URLSearchParams(formData).toString();
                            headers = {
                                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                            };
                        }

                        const csrf = obterCsrfToken();
                        if (csrf) {
                            if (!headers) {
                                headers = {};
                            }
                            headers['X-CSRF-Token'] = csrf;
                        }

                        try {
                            const resposta = await fetch(formulario.action, {
                                method: metodoRest,
                                headers,
                                body: corpo
                            });

                            let retorno = null;
                            try {
                                retorno = await resposta.json();
                            } catch (e) {
                                retorno = null;
                            }

                            const sucesso = Boolean(retorno && typeof retorno === 'object' && retorno.sucesso);
                            const mensagem = String(retorno && typeof retorno === 'object' && retorno.mensagem ? retorno.mensagem : (resposta.ok ? 'Operação realizada.' : 'Erro na operação.'));

                            salvarFlash(sucesso ? 'success' : 'danger', mensagem);

                            if (redirecionar) {
                                const url = new URL(redirecionar, window.location.origin);
                                window.location.href = url.toString();
                                return;
                            }

                            window.location.reload();
                        } catch (e) {
                            alert('Falha ao enviar a requisição.');
                        }
                    });
                })();
            </script>
        </body>
        </html>
        <?php
    }
}
