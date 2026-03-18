<?php

class Posts
{
    private function obterId(array $segmentosUrl): int
    {
        if (isset($segmentosUrl[1]) && ctype_digit((string)$segmentosUrl[1])) {
            return (int)$segmentosUrl[1];
        }

        return 0;
    }

    private function conectar(): PDO
    {
        return (new Database())->conectar();
    }

    private function renderErroConexao(Exception $e): void
    {
        http_response_code(500);
        Layout::topo('Erro');
        echo '<div class="alert alert-danger">Falha ao conectar no banco de dados.</div>';
        echo '<pre class="small text-muted mb-0">' . Http::e($e->getMessage()) . '</pre>';
        Layout::rodape();
    }

    private function conectarOuRenderErro(): ?PDO
    {
        try {
            return $this->conectar();
        } catch (Exception $e) {
            $this->renderErroConexao($e);
            return null;
        }
    }

    private function usuarioPorId(array $usuarios): array
    {
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

        return $usuarioPorId;
    }

    private function categoriaPorId(array $categorias): array
    {
        $categoriaPorId = [];
        foreach ($categorias as $categoria) {
            $id = (int)($categoria['id'] ?? 0);
            if ($id <= 0) {
                continue;
            }

            $categoriaPorId[$id] = (string)($categoria['nome'] ?? '');
        }

        return $categoriaPorId;
    }

    private function renderPostNaoEncontrado(): void
    {
        http_response_code(404);
        Layout::topo('Post não encontrado');
        echo '<div class="alert alert-warning">Post não encontrado.</div>';
        echo '<a class="btn btn-primary" href="' . Http::e(Http::baseUrl('/inicio')) . '">Ir para Início</a>';
        Layout::rodape();
    }

    public function get(array $segmentosUrl): void
    {
        $idDetalhes = $this->obterId($segmentosUrl);
        if ($idDetalhes > 0) {
            $this->renderDetalhes($idDetalhes);
            return;
        }

        $conexao = $this->conectarOuRenderErro();
        if (!$conexao) {
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

        $usuarioPorId = $this->usuarioPorId($usuarios);
        $categoriaPorId = $this->categoriaPorId($categorias);

        $acaoFormulario = Http::baseUrl('/posts');

        $rota = strtolower((string)($segmentosUrl[0] ?? ''));
        $tituloPagina = $rota === 'inicio' ? "Filmmakers' Blog" : 'Início';

        Layout::topo($tituloPagina);

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

    private function renderDetalhes(int $idPost): void
    {
        $conexao = $this->conectarOuRenderErro();
        if (!$conexao) {
            return;
        }

        $modeloPost = new Post($conexao);
        $modeloCategoria = new Categoria($conexao);
        $modeloUsuario = new Usuario($conexao);
        $modeloComentario = new Comentario($conexao);

        $post = $modeloPost->get($idPost);
        if (!$post) {
            $this->renderPostNaoEncontrado();
            return;
        }

        $usuarios = $modeloUsuario->get();
        $categorias = $modeloCategoria->get();
        $comentarios = $modeloComentario->listarPorPostId($idPost);

        $usuarioPorId = $this->usuarioPorId($usuarios);
        $categoriaPorId = $this->categoriaPorId($categorias);

        $titulo = (string)($post['titulo'] ?? 'Post');
        Layout::topo($titulo !== '' ? $titulo : 'Post');

        require_once __DIR__ . '/../views/post_detalhes.php';
        PostDetalhesView::render([
            'post' => $post,
            'comentarios' => $comentarios,
            'usuarioPorId' => $usuarioPorId,
            'categoriaPorId' => $categoriaPorId,
            'usuarioAutenticado' => !empty($_SESSION['usuario_id']),
            'urlVoltar' => Http::baseUrl('/inicio'),
            'urlAdicionarComentario' => Http::baseUrl('/posts/' . $idPost . '/comentarios'),
            'urlLogin' => Http::baseUrl('/login'),
        ]);

        Layout::rodape();
    }

    public function post(array $segmentosUrl): void
    {
        $id = $this->obterId($segmentosUrl);

        $subrecurso = strtolower((string)($segmentosUrl[2] ?? ''));
        if ($id > 0 && $subrecurso === 'comentarios') {
            $this->criarComentarioParaPost($id);
            return;
        }

        if ($id > 0) {
            http_response_code(405);
            echo 'Método HTTP não suportado.';
            return;
        }

        $conexao = $this->conectarOuRenderErro();
        if (!$conexao) {
            return;
        }

        $modeloPost = new Post($conexao);

        $dadosPost = Http::limparArray((array)$_POST);
        $sucesso = (bool)$modeloPost->post($dadosPost);
        $mensagem = $sucesso ? 'Post criado com sucesso.' : 'Erro ao criar post.';

        Http::setFlash($mensagem, $sucesso ? 'success' : 'danger');
        Http::redirect(Http::baseUrl('/adicionar-posts'), 303);
        exit;
    }

    private function criarComentarioParaPost(int $idPost): void
    {
        $usuarioId = (int)($_SESSION['usuario_id'] ?? 0);
        if ($usuarioId <= 0) {
            Http::setFlash('Faça login para comentar.', 'danger');
            Http::redirect(Http::baseUrl('/login'), 303);
            exit;
        }

        $dadosPost = Http::limparArray((array)$_POST);
        $texto = trim((string)($dadosPost['texto'] ?? ''));

        if ($texto === '') {
            Http::setFlash('O comentário não pode estar vazio.', 'danger');
            Http::redirect(Http::baseUrl('/posts/' . $idPost), 303);
            exit;
        }

        $conexao = $this->conectarOuRenderErro();
        if (!$conexao) {
            return;
        }

        $modeloPost = new Post($conexao);
        $post = $modeloPost->get($idPost);
        if (!$post) {
            $this->renderPostNaoEncontrado();
            return;
        }

        $modeloComentario = new Comentario($conexao);
        $sucesso = (bool)$modeloComentario->post([
            'post_id' => $idPost,
            'autor_id' => $usuarioId,
            'texto' => $texto,
        ]);

        $mensagem = $sucesso ? 'Comentário adicionado com sucesso.' : 'Erro ao adicionar comentário.';

        Http::setFlash($mensagem, $sucesso ? 'success' : 'danger');
        Http::redirect(Http::baseUrl('/posts/' . $idPost), 303);
        exit;
    }

    public function put(array $segmentosUrl): void
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

    public function delete(array $segmentosUrl): void
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
