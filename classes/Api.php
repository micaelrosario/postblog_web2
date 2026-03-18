<?php

class Api
{
    private function cors()
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

    private function apiLogin(): void
    {
        $metodo = strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET'));
        if ($metodo !== 'POST') {
            Http::jsonResponse(['sucesso' => false, 'mensagem' => 'Método HTTP não suportado.'], 405);
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

            $this->garantirSessaoIniciada();
            session_regenerate_id(true);
            $_SESSION['usuario_id'] = (int)($usuario['id'] ?? 0);
            $_SESSION['username'] = (string)($usuario['username'] ?? '');
            $_SESSION['login'] = (string)($usuario['username'] ?? '');
            // Mantém "senha" na sessão conforme solicitado (armazenando o HASH do banco, não a senha em texto).
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

    private function apiLogout(): void
    {
        $metodo = strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET'));
        if ($metodo !== 'POST') {
            Http::jsonResponse(['sucesso' => false, 'mensagem' => 'Método HTTP não suportado.'], 405);
            return;
        }

        $this->garantirSessaoIniciada();
        $_SESSION = [];

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }

        Http::jsonResponse(['sucesso' => true, 'mensagem' => 'Logout efetuado.'], 200);
    }

    public function options($segmentosUrl)
    {
        $this->cors();
        http_response_code(204);
    }

    public function get($segmentosUrl)
    {
        $this->handle($segmentosUrl);
    }

    public function post($segmentosUrl)
    {
        $this->handle($segmentosUrl);
    }

    public function put($segmentosUrl)
    {
        $this->handle($segmentosUrl);
    }

    public function delete($segmentosUrl)
    {
        $this->handle($segmentosUrl);
    }

    private function handle($segmentosUrl)
    {
        $this->cors();

        $this->garantirSessaoIniciada();

        // Recurso vem de /api/{recurso} (recomendado) ou ?resource=... (compatibilidade).
        $recurso = '';
        if (isset($segmentosUrl[1]) && $segmentosUrl[1] !== '') {
            $recurso = strtolower(trim((string)$segmentosUrl[1]));
        }
        if ($recurso === '') {
            $recurso = strtolower(trim((string)($_GET['resource'] ?? '')));
        }

        if ($recurso === 'login') {
            $this->apiLogin();
            return;
        }

        if ($recurso === 'logout') {
            $this->apiLogout();
            return;
        }

        $mapaRecursos = [
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

        if ($recurso === '' || !isset($mapaRecursos[$recurso])) {
            Http::jsonResponse([
                'sucesso' => false,
                'mensagem' => 'Recurso inválido. Use /api/posts|categorias|usuarios|comentarios|perfil_autor ou ?resource=...',
            ], 404);
            return;
        }

        $config = $mapaRecursos[$recurso];

        $metodo = strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET'));

        if ($metodo === 'HEAD') {
            $metodo = 'GET';
        }

        if (empty($_SESSION['usuario_id']) && $metodo !== 'GET') {
            Http::jsonResponse([
                'sucesso' => false,
                'mensagem' => 'Não autenticado.',
            ], 401);
            return;
        }

        try {
            $conexao = (new Database())->conectar();

            $nomeClasse = (string)$config['class'];
            $modelo = new $nomeClasse($conexao);

            // ID pode vir de /api/{recurso}/{id} ou de ?id=... (compatibilidade).
            $id = null;
            if (isset($segmentosUrl[2]) && $segmentosUrl[2] !== '' && ctype_digit((string)$segmentosUrl[2])) {
                $id = (int)$segmentosUrl[2];
            }
            if ($id === null && isset($_GET['id'])) {
                $id = (int)$_GET['id'];
            }

            if ($metodo === 'GET') {
                $resultado = $modelo->get($id);

                if ($id !== null) {
                    if ($resultado === null) {
                        Http::jsonResponse(['erro' => $config['notFound']], 200);
                        return;
                    }
                    Http::jsonResponse($resultado, 200);
                    return;
                }

                Http::jsonResponse($resultado, 200);
                return;
            }

            if ($metodo === 'POST') {
                $dadosPost = !empty($_POST) ? (array)$_POST : (array)Http::lerDadosCorpo();
                $dadosPost = Http::limparArray((array)$dadosPost, ['naoTrim' => ['senha']]);

                $sucesso = (bool)$modelo->post($dadosPost);
                Http::jsonResponse([
                    'sucesso' => $sucesso,
                    'mensagem' => $sucesso ? $config['messages']['post_ok'] : $config['messages']['post_fail'],
                ], 200);
                return;
            }

            if ($metodo === 'PUT') {
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
                return;
            }

            if ($metodo === 'DELETE') {
                if ($id === null) {
                    Http::jsonResponse(['sucesso' => false, 'mensagem' => 'Parâmetro id é obrigatório.'], 400);
                    return;
                }

                $sucesso = (bool)$modelo->delete($id);

                Http::jsonResponse([
                    'sucesso' => $sucesso,
                    'mensagem' => $sucesso ? $config['messages']['delete_ok'] : $config['messages']['delete_fail'],
                ], 200);
                return;
            }

            Http::jsonResponse(['sucesso' => false, 'mensagem' => 'Método HTTP não suportado.'], 405);
        } catch (Exception $e) {
            Http::jsonResponse([
                'sucesso' => false,
                'mensagem' => 'Erro: ' . $e->getMessage(),
            ], 500);
        }
    }
}
