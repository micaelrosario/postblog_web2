<?php

class Api
{
    private const MSG_METODO_NAO_SUPORTADO = 'Método HTTP não suportado.';
    private const MSG_NAO_AUTENTICADO = 'Não autenticado.';
    private const MSG_RECURSO_INVALIDO = 'Recurso inválido. Use /api/posts|categorias|usuarios|comentarios|perfil_autor ou ?resource=...';

    private const MAPA_RECURSOS = [
        'posts' => [
            'class' => 'Post',
            'notFound' => 'Post não encontrado',
            'messages' => [
                'post_ok' => 'Post criado com sucesso',
                'post_fail' => 'Erro ao criar post',
                'put_ok' => 'Post atualizado com sucesso',
                'put_fail' => 'Erro ao atualizar post',
                'delete_ok' => 'Post removido com sucesso',
                'delete_fail' => 'Erro ao remover post',
            ],
        ],
        'categorias' => [
            'class' => 'Categoria',
            'notFound' => 'Categoria não encontrada',
            'messages' => [
                'post_ok' => 'Categoria criada com sucesso',
                'post_fail' => 'Erro ao criar categoria',
                'put_ok' => 'Categoria atualizada com sucesso',
                'put_fail' => 'Erro ao atualizar categoria',
                'delete_ok' => 'Categoria removida com sucesso',
                'delete_fail' => 'Erro ao remover categoria',
            ],
        ],
        'usuarios' => [
            'class' => 'Usuario',
            'notFound' => 'Usuário não encontrado',
            'messages' => [
                'post_ok' => 'Usuário criado com sucesso',
                'post_fail' => 'Erro ao criar usuário',
                'put_ok' => 'Usuário atualizado com sucesso',
                'put_fail' => 'Erro ao atualizar usuário',
                'delete_ok' => 'Usuário removido com sucesso',
                'delete_fail' => 'Erro ao remover usuário',
            ],
        ],
        'comentarios' => [
            'class' => 'Comentario',
            'notFound' => 'Comentário não encontrado',
            'messages' => [
                'post_ok' => 'Comentário criado com sucesso',
                'post_fail' => 'Erro ao criar comentário',
                'put_ok' => 'Comentário atualizado com sucesso',
                'put_fail' => 'Erro ao atualizar comentário',
                'delete_ok' => 'Comentário removido com sucesso',
                'delete_fail' => 'Erro ao remover comentário',
            ],
        ],
        'perfil_autor' => [
            'class' => 'PerfilAutor',
            'notFound' => 'Perfil não encontrado',
            'messages' => [
                'post_ok' => 'Perfil criado com sucesso',
                'post_fail' => 'Erro ao criar perfil',
                'put_ok' => 'Perfil atualizado com sucesso',
                'put_fail' => 'Erro ao atualizar perfil',
                'delete_ok' => 'Perfil removido com sucesso',
                'delete_fail' => 'Erro ao remover perfil',
            ],
        ],
    ];

    private function cors(): void
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');
    }

    private function garantirSessaoIniciada(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    private function apiLogin(string $metodo): void
    {
        if ($metodo !== 'POST') {
            Http::jsonResponse(['sucesso' => false, 'mensagem' => self::MSG_METODO_NAO_SUPORTADO], 405);
            return;
        }

        $dados = Http::lerDadosCorpo();
        if (empty($dados)) {
            $dados = (array)$_POST;
        }

        $dados = Http::limparArray((array)$dados, ['naoTrim' => ['senha']]);

        $username = trim((string)($dados['username'] ?? ''));
        $senha = (string)($dados['senha'] ?? '');

        if ($username === '' || $senha === '') {
            Http::jsonResponse(['sucesso' => false, 'mensagem' => 'Username e senha são obrigatórios.'], 400);
            return;
        }

        try {
            $conexao = (new Database())->conectar();
            $modeloUsuario = new Usuario($conexao);

            $usuario = $modeloUsuario->buscarPorUsername($username);

            if (!$usuario || !password_verify($senha, (string)($usuario['senha'] ?? ''))) {
                Http::jsonResponse(['sucesso' => false, 'mensagem' => 'Credenciais inválidas.'], 401);
                return;
            }

            session_regenerate_id(true);
            $_SESSION['usuario_id'] = (int)($usuario['id'] ?? 0);
            $_SESSION['username'] = (string)($usuario['username'] ?? '');
            $_SESSION['login'] = (string)($usuario['username'] ?? '');
            $_SESSION['senha'] = (string)($usuario['senha'] ?? '');

            Http::jsonResponse([
                'sucesso' => true,
                'mensagem' => 'Login efetuado.',
                'usuario' => [
                    'id' => (int)($usuario['id'] ?? 0),
                    'username' => (string)($usuario['username'] ?? ''),
                ],
            ], 200);
        } catch (Exception $e) {
            Http::jsonResponse(['sucesso' => false, 'mensagem' => 'Erro ao efetuar login.'], 500);
        }
    }

    private function apiLogout(string $metodo): void
    {
        if ($metodo !== 'POST') {
            Http::jsonResponse(['sucesso' => false, 'mensagem' => self::MSG_METODO_NAO_SUPORTADO], 405);
            return;
        }

        $_SESSION = [];

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }

        Http::jsonResponse(['sucesso' => true, 'mensagem' => 'Logout efetuado.'], 200);
    }

    public function options(array $segmentosUrl): void
    {
        $this->cors();
        http_response_code(204);
    }

    public function get(array $segmentosUrl): void
    {
        $this->handle($segmentosUrl);
    }

    public function post(array $segmentosUrl): void
    {
        $this->handle($segmentosUrl);
    }

    public function put(array $segmentosUrl): void
    {
        $this->handle($segmentosUrl);
    }

    public function delete(array $segmentosUrl): void
    {
        $this->handle($segmentosUrl);
    }

    private function handle(array $segmentosUrl): void
    {
        $this->cors();

        $this->garantirSessaoIniciada();

        $metodo = $this->metodoHttp();

        // Recurso vem de /api/{recurso} (recomendado) ou ?resource=... (compatibilidade).
        $recurso = $this->recursoAtual($segmentosUrl);

        if ($recurso === 'login') {
            $this->apiLogin($metodo);
            return;
        }

        if ($recurso === 'logout') {
            $this->apiLogout($metodo);
            return;
        }

        $config = self::MAPA_RECURSOS[$recurso] ?? null;
        if ($recurso === '' || !is_array($config)) {
            $this->responderRecursoInvalido();
            return;
        }

        if (empty($_SESSION['usuario_id']) && $metodo !== 'GET') {
            $this->responderNaoAutenticado();
            return;
        }

        try {
            $conexao = (new Database())->conectar();

            $nomeClasse = (string)$config['class'];
            $modelo = new $nomeClasse($conexao);

            // ID pode vir de /api/{recurso}/{id} ou de ?id=... (compatibilidade).
            $id = $this->obterId($segmentosUrl);

            $this->dispatch($metodo, $modelo, $id, $config);
        } catch (Exception $e) {
            Http::jsonResponse([
                'sucesso' => false,
                'mensagem' => 'Erro: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function metodoHttp(): string
    {
        $metodo = strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET'));
        return $metodo === 'HEAD' ? 'GET' : $metodo;
    }

    private function recursoAtual(array $segmentosUrl): string
    {
        $recurso = strtolower(trim((string)($segmentosUrl[1] ?? '')));
        if ($recurso === '') {
            $recurso = strtolower(trim((string)($_GET['resource'] ?? '')));
        }

        return $recurso;
    }

    private function obterId(array $segmentosUrl): ?int
    {
        $id = null;
        if (isset($segmentosUrl[2]) && $segmentosUrl[2] !== '' && ctype_digit((string)$segmentosUrl[2])) {
            $id = (int)$segmentosUrl[2];
        }
        if ($id === null && isset($_GET['id'])) {
            $id = (int)$_GET['id'];
        }

        return $id;
    }

    private function responderRecursoInvalido(): void
    {
        Http::jsonResponse([
            'sucesso' => false,
            'mensagem' => self::MSG_RECURSO_INVALIDO,
        ], 404);
    }

    private function responderNaoAutenticado(): void
    {
        Http::jsonResponse([
            'sucesso' => false,
            'mensagem' => self::MSG_NAO_AUTENTICADO,
        ], 401);
    }

    private function dispatch(string $metodo, object $modelo, ?int $id, array $config): void
    {
        switch ($metodo) {
            case 'GET':
                $this->dispatchGet($modelo, $id, $config);
                return;

            case 'POST':
                $this->dispatchPost($modelo, $config);
                return;

            case 'PUT':
                $this->dispatchPut($modelo, $id, $config);
                return;

            case 'DELETE':
                $this->dispatchDelete($modelo, $id, $config);
                return;

            default:
                Http::jsonResponse(['sucesso' => false, 'mensagem' => self::MSG_METODO_NAO_SUPORTADO], 405);
                return;
        }
    }

    private function dispatchGet(object $modelo, ?int $id, array $config): void
    {
        $resultado = $modelo->get($id);

        if ($id !== null && $resultado === null) {
            Http::jsonResponse(['erro' => $config['notFound']], 200);
            return;
        }

        Http::jsonResponse($resultado, 200);
    }

    private function dispatchPost(object $modelo, array $config): void
    {
        $dadosPost = !empty($_POST) ? (array)$_POST : (array)Http::lerDadosCorpo();
        $dadosPost = Http::limparArray((array)$dadosPost, ['naoTrim' => ['senha']]);

        $sucesso = (bool)$modelo->post($dadosPost);
        Http::jsonResponse([
            'sucesso' => $sucesso,
            'mensagem' => $sucesso ? $config['messages']['post_ok'] : $config['messages']['post_fail'],
        ], 200);
    }

    private function dispatchPut(object $modelo, ?int $id, array $config): void
    {
        if ($id === null) {
            Http::jsonResponse(['sucesso' => false, 'mensagem' => 'Parâmetro id é obrigatório.'], 400);
            return;
        }

        $dadosPut = Http::lerDadosCorpoLimpo(['naoTrim' => ['senha']]);

        $sucesso = (bool)$modelo->put($id, (array)$dadosPut);

        Http::jsonResponse([
            'sucesso' => $sucesso,
            'mensagem' => $sucesso ? $config['messages']['put_ok'] : $config['messages']['put_fail'],
        ], 200);
    }

    private function dispatchDelete(object $modelo, ?int $id, array $config): void
    {
        if ($id === null) {
            Http::jsonResponse(['sucesso' => false, 'mensagem' => 'Parâmetro id é obrigatório.'], 400);
            return;
        }

        $sucesso = (bool)$modelo->delete($id);

        Http::jsonResponse([
            'sucesso' => $sucesso,
            'mensagem' => $sucesso ? $config['messages']['delete_ok'] : $config['messages']['delete_fail'],
        ], 200);
    }
}
