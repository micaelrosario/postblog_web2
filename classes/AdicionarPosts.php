<?php

class AdicionarPosts
{
    private function conectar()
    {
        return (new Database())->conectar();
    }

    public function get($segmentosUrl)
    {
        $usuarioAutenticado = !empty($_SESSION['usuario_id']);
        if (!$usuarioAutenticado) {
            Http::redirect(
                Http::baseUrl('/login') . '?ok=0&msg=' . rawurlencode('Faça login para continuar.'),
                303
            );
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
        $modeloCategoria = new Categoria($conexao);
        $modeloUsuario = new Usuario($conexao);

        $idEdicao = (int)($_GET['edit'] ?? 0);
        $postEdicao = $idEdicao > 0 ? $modeloPost->get($idEdicao) : null;

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

        Layout::topo('Adicionar Post');

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
            'usuarioAutenticado' => true,
            'urlLeitor' => Http::baseUrl('/posts'),
            'urlAdmin' => Http::baseUrl('/adicionar-posts'),
        ]);

        Layout::rodape();
    }
}
