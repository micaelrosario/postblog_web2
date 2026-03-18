<?php

class AdicionarPosts
{
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

    public function get(array $segmentosUrl): void
    {
        $usuarioAutenticado = !empty($_SESSION['usuario_id']);
        if (!$usuarioAutenticado) {
            Http::setFlash('Faça login para continuar.', 'danger');
            Http::redirect(Http::baseUrl('/login'), 303);
            return;
        }

        $conexao = $this->conectarOuRenderErro();
        if (!$conexao) {
            return;
        }

        $modeloPost = new Post($conexao);
        $modeloCategoria = new Categoria($conexao);
        $modeloUsuario = new Usuario($conexao);

        $idEdicao = (int)($_GET['edit'] ?? 0);
        $postEdicao = $idEdicao > 0 ? $modeloPost->get($idEdicao) : null;

        $usuarios = $modeloUsuario->get();
        $categorias = $modeloCategoria->get();
        $posts = $modeloPost->get();

        $usuarioPorId = $this->usuarioPorId($usuarios);
        $categoriaPorId = $this->categoriaPorId($categorias);

        $acaoFormulario = $postEdicao
            ? Http::baseUrl('/posts/' . (int)($postEdicao['id'] ?? 0))
            : Http::baseUrl('/posts');

        Layout::topo('Adicionar Post');

        require_once __DIR__ . '/../views/posts.php';
        PostsView::render([
            'postEdicao' => $postEdicao,
            'usuarios' => $usuarios,
            'categorias' => $categorias,
            'posts' => $posts,
            'usuarioPorId' => $usuarioPorId,
            'categoriaPorId' => $categoriaPorId,
            'acaoFormulario' => $acaoFormulario,
            'usuarioAutenticado' => true,
            'urlLeitor' => Http::baseUrl('/posts'),
            'urlAdmin' => Http::baseUrl('/adicionar-posts'),
        ]);

        Layout::rodape();
    }
}
