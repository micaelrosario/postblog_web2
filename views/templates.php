<?php

function baseUrl($caminho = '')
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

function e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function jsonResponse($dados, $codigoStatus = 200)
{
    http_response_code((int)$codigoStatus);
    header('Content-Type: application/json; charset=UTF-8');

    echo json_encode($dados, JSON_UNESCAPED_UNICODE);
}

function lerDadosCorpo(): array
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

function topo($titulo)
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

function rodape()
{
    ?>
        </main>

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
