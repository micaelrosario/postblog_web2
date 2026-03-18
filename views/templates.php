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
            <title><?php echo Http::e($titulo); ?></title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        </head>
        <body class="bg-light d-flex flex-column min-vh-100">
            <?php if ($mostrarNavbar) { ?>
                <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
                    <div class="container">
                        <a class="navbar-brand" href="<?php echo Http::e(Http::baseUrl('/inicio')); ?>">BlogPost</a>
                        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav" aria-controls="nav" aria-expanded="false" aria-label="Toggle navigation">
                            <span class="navbar-toggler-icon"></span>
                        </button>
                        <div class="collapse navbar-collapse" id="nav">
                            <div class="navbar-nav">
                                <a class="nav-link" href="<?php echo Http::e(Http::baseUrl('/inicio')); ?>">Início</a>
                                <?php if (!empty($_SESSION['usuario_id'])) { ?>
                                    <a class="nav-link" href="<?php echo Http::e(Http::baseUrl('/adicionar-posts')); ?>">Adicionar post</a>
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

                            if (redirecionar) {
                                const url = new URL(redirecionar, window.location.origin);
                                url.searchParams.set('ok', sucesso ? '1' : '0');
                                url.searchParams.set('msg', mensagem);
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
