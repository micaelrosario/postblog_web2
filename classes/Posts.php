<?php

declare(strict_types=1);

defined('ACCESS') or die('Acesso negado');

class Posts
{
    use Template;

    private function conectar(): PDO
    {
        return (new Database())->conectar();
    }

    public function get(array $dados): void
    {
        try {
            $con = $this->conectar();
        } catch (Throwable $e) {
            http_response_code(500);
            $this->topo('Erro');
            echo '<div class="alert alert-danger">Falha ao conectar no banco de dados.</div>';
            echo '<pre class="small text-muted mb-0">' . e($e->getMessage()) . '</pre>';
            $this->rodape();
            return;
        }

        $postModel = new Post($con);
        $categoriaModel = new Categoria($con);
        $usuarioModel = new Usuario($con);

        $this->topo('Posts');

        $msg = (string)($_GET['msg'] ?? '');
        if ($msg !== '') {
            $ok = (string)($_GET['ok'] ?? '0');
            $type = $ok === '1' ? 'success' : 'danger';
            echo '<div class="alert alert-' . e($type) . '">' . e($msg) . '</div>';
        }

        require __DIR__ . '/../views/posts.php';

        $this->rodape();
    }

    public function post(array $dados): void
    {
        try {
            $con = $this->conectar();
        } catch (Throwable $e) {
            http_response_code(500);
            $this->topo('Erro');
            echo '<div class="alert alert-danger">Falha ao conectar no banco de dados.</div>';
            echo '<pre class="small text-muted mb-0">' . e($e->getMessage()) . '</pre>';
            $this->rodape();
            return;
        }

        $postModel = new Post($con);

        $action = (string)($_POST['action'] ?? '');
        $ok = false;
        $msg = 'Ação inválida.';

        if ($action === 'create') {
            $ok = (bool)$postModel->post($_POST);
            $msg = $ok ? 'Post criado com sucesso.' : 'Erro ao criar post.';
        } elseif ($action === 'update') {
            $id = (int)($_POST['id'] ?? 0);
            $ok = $id > 0 ? (bool)$postModel->put($id, $_POST) : false;
            $msg = $ok ? 'Post atualizado com sucesso.' : 'Erro ao atualizar post.';
        } elseif ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            $ok = $id > 0 ? (bool)$postModel->delete($id) : false;
            $msg = $ok ? 'Post removido com sucesso.' : 'Erro ao remover post.';
        }

        header('Location: ' . baseUrl('/posts') . '?ok=' . ($ok ? '1' : '0') . '&msg=' . rawurlencode($msg), true, 303);
        exit;
    }

    public function put(array $dados): void
    {
        http_response_code(405);
        echo 'Método HTTP não suportado.';
    }

    public function delete(array $dados): void
    {
        http_response_code(405);
        echo 'Método HTTP não suportado.';
    }
}
