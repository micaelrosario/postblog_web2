<?php

declare(strict_types=1);

defined('ACCESS') or die('Acesso negado');

class Comentarios
{
    use Template;

    private function conectar(): PDO
    {
        return (new Database())->conectar();
    }

    public function get(array $segmentosUrl): void
    {
        try {
            $conexao = $this->conectar();
        } catch (Throwable $e) {
            http_response_code(500);
            $this->topo('Erro');
            echo '<div class="alert alert-danger">Falha ao conectar no banco de dados.</div>';
            echo '<pre class="small text-muted mb-0">' . e($e->getMessage()) . '</pre>';
            $this->rodape();
            return;
        }

        $modeloComentario = new Comentario($conexao);
        $modeloPost = new Post($conexao);
        $modeloUsuario = new Usuario($conexao);

        $this->topo('Comentários');

        $mensagem = (string)($_GET['msg'] ?? '');
        if ($mensagem !== '') {
            $okUrl = (string)($_GET['ok'] ?? '0');
            $tipo = $okUrl === '1' ? 'success' : 'danger';
            echo '<div class="alert alert-' . e($tipo) . '">' . e($mensagem) . '</div>';
        }

        require __DIR__ . '/../views/comentarios.php';

        $this->rodape();
    }

    public function post(array $segmentosUrl): void
    {
        try {
            $conexao = $this->conectar();
        } catch (Throwable $e) {
            http_response_code(500);
            $this->topo('Erro');
            echo '<div class="alert alert-danger">Falha ao conectar no banco de dados.</div>';
            echo '<pre class="small text-muted mb-0">' . e($e->getMessage()) . '</pre>';
            $this->rodape();
            return;
        }

        $modeloComentario = new Comentario($conexao);

        $acao = (string)($_POST['action'] ?? '');
        $sucesso = false;
        $mensagem = 'Ação inválida.';

        if ($acao === 'create') {
            $sucesso = (bool)$modeloComentario->post($_POST);
            $mensagem = $sucesso ? 'Comentário criado com sucesso.' : 'Erro ao criar comentário.';
        } elseif ($acao === 'update') {
            $id = (int)($_POST['id'] ?? 0);
            $sucesso = $id > 0 ? (bool)$modeloComentario->put($id, $_POST) : false;
            $mensagem = $sucesso ? 'Comentário atualizado com sucesso.' : 'Erro ao atualizar comentário.';
        } elseif ($acao === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            $sucesso = $id > 0 ? (bool)$modeloComentario->delete($id) : false;
            $mensagem = $sucesso ? 'Comentário removido com sucesso.' : 'Erro ao remover comentário.';
        }

        header('Location: ' . baseUrl('/comentarios') . '?ok=' . ($sucesso ? '1' : '0') . '&msg=' . rawurlencode($mensagem), true, 303);
        exit;
    }

    public function put(array $segmentosUrl): void
    {
        http_response_code(405);
        echo 'Método HTTP não suportado.';
    }

    public function delete(array $segmentosUrl): void
    {
        http_response_code(405);
        echo 'Método HTTP não suportado.';
    }
}
