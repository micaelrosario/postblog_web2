<?php

class Posts
{
    private function obterId($segmentosUrl)
    {
        if (isset($segmentosUrl[1]) && ctype_digit((string)$segmentosUrl[1])) {
            return (int)$segmentosUrl[1];
        }

        return 0;
    }

    private function conectar()
    {
        return (new Database())->conectar();
    }

    public function get($segmentosUrl)
    {
        try {
            $conexao = $this->conectar();
        } catch (Exception $e) {
            http_response_code(500);
            Layout::topo('Erro');
            echo '<div class="alert alert-danger">Falha ao conectar no banco de dados.</div>';
            echo '<pre class="small text-muted mb-0">' . Http::e($e->getMessage()) . '</pre>';
            Layout::rodape();
            return;
        }

        $modeloPost = new Post($conexao);
        $modeloCategoria = new Categoria($conexao);
        $modeloUsuario = new Usuario($conexao);

        // Visão do leitor: não exibe formulário nem ações, mesmo se estiver logado.
        $usuarioAutenticado = false;
        $postEdicao = null;

        $usuarios = $modeloUsuario->get();
        $categorias = $modeloCategoria->get();
        $posts = $modeloPost->get();

        $usuarioPorId = [];
        foreach ($usuarios as $usuario) {
            $id = (int)($usuario['id'] ?? 0);
            if ($id <= 0) {
                continue;
            }

            $nomeExibicao = (string)($usuario['username'] ?? '');
            if ($nomeExibicao === '') {
                $nome = trim((string)($usuario['first_name'] ?? '') . ' ' . (string)($usuario['last_name'] ?? ''));
                $nomeExibicao = $nome !== '' ? $nome : ('Usuário #' . $id);
            }

            $usuarioPorId[$id] = $nomeExibicao;
        }

        $categoriaPorId = [];
        foreach ($categorias as $categoria) {
            $id = (int)($categoria['id'] ?? 0);
            if ($id <= 0) {
                continue;
            }

            $categoriaPorId[$id] = (string)($categoria['nome'] ?? '');
        }

        $acaoFormulario = $postEdicao
            ? Http::baseUrl('/posts/' . (int)($postEdicao['id'] ?? 0))
            : Http::baseUrl('/posts');

        Layout::topo('Início');

        $mensagem = (string)($_GET['msg'] ?? '');
        if ($mensagem !== '') {
            $okUrl = (string)($_GET['ok'] ?? '0');
            $tipo = $okUrl === '1' ? 'success' : 'danger';
            echo '<div class="alert alert-' . Http::e($tipo) . '">' . Http::e($mensagem) . '</div>';
        }

        require_once __DIR__ . '/../views/posts.php';
        PostsView::render([
            'postEdicao' => $postEdicao,
            'usuarios' => $usuarios,
            'categorias' => $categorias,
            'posts' => $posts,
            'usuarioPorId' => $usuarioPorId,
            'categoriaPorId' => $categoriaPorId,
            'acaoFormulario' => $acaoFormulario,
            'usuarioAutenticado' => $usuarioAutenticado,
            'urlLeitor' => Http::baseUrl('/posts'),
            'urlAdmin' => Http::baseUrl('/adicionar-posts'),
        ]);

        Layout::rodape();
    }

    public function post($segmentosUrl)
    {
        $id = $this->obterId($segmentosUrl);
        if ($id > 0) {
            http_response_code(405);
            echo 'Método HTTP não suportado.';
            return;
        }

        try {
            $conexao = $this->conectar();
        } catch (Exception $e) {
            http_response_code(500);
            Layout::topo('Erro');
            echo '<div class="alert alert-danger">Falha ao conectar no banco de dados.</div>';
            echo '<pre class="small text-muted mb-0">' . Http::e($e->getMessage()) . '</pre>';
            Layout::rodape();
            return;
        }

        $modeloPost = new Post($conexao);

        $dadosPost = Http::limparArray((array)$_POST);
        $sucesso = (bool)$modeloPost->post($dadosPost);
        $mensagem = $sucesso ? 'Post criado com sucesso.' : 'Erro ao criar post.';

        header('Location: ' . Http::baseUrl('/adicionar-posts') . '?ok=' . ($sucesso ? '1' : '0') . '&msg=' . rawurlencode($mensagem), true, 303);
        exit;
    }

    public function put($segmentosUrl)
    {
        $id = $this->obterId($segmentosUrl);
        if ($id <= 0) {
            Http::jsonResponse(['sucesso' => false, 'mensagem' => 'Parâmetro id é obrigatório.'], 400);
            return;
        }

        try {
            $conexao = $this->conectar();
            $modeloPost = new Post($conexao);

            $dadosPut = Http::lerDadosCorpoLimpo();

            $sucesso = (bool)$modeloPost->put($id, $dadosPut);
            $mensagem = $sucesso ? 'Post atualizado com sucesso.' : 'Erro ao atualizar post.';

            Http::jsonResponse(['sucesso' => $sucesso, 'mensagem' => $mensagem], 200);
        } catch (Exception $e) {
            Http::jsonResponse(['sucesso' => false, 'mensagem' => 'Erro: ' . $e->getMessage()], 500);
        }
    }

    public function delete($segmentosUrl)
    {
        $id = $this->obterId($segmentosUrl);
        if ($id <= 0) {
            Http::jsonResponse(['sucesso' => false, 'mensagem' => 'Parâmetro id é obrigatório.'], 400);
            return;
        }

        try {
            $conexao = $this->conectar();
            $modeloPost = new Post($conexao);

            $sucesso = (bool)$modeloPost->delete($id);
            $mensagem = $sucesso ? 'Post removido com sucesso.' : 'Erro ao remover post.';

            Http::jsonResponse(['sucesso' => $sucesso, 'mensagem' => $mensagem], 200);
        } catch (Exception $e) {
            Http::jsonResponse(['sucesso' => false, 'mensagem' => 'Erro: ' . $e->getMessage()], 500);
        }
    }
}
