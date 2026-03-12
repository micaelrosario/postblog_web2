<?php

declare(strict_types=1);

defined('ACCESS') or die('Acesso negado');

class Usuarios
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

        $modeloUsuario = new Usuario($conexao);

        $this->topo('Usuários');

        $mensagem = (string)($_GET['msg'] ?? '');
        if ($mensagem !== '') {
            $okUrl = (string)($_GET['ok'] ?? '0');
            $tipo = $okUrl === '1' ? 'success' : 'danger';
            echo '<div class="alert alert-' . e($tipo) . '">' . e($mensagem) . '</div>';
        }

        require __DIR__ . '/../views/usuarios.php';

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

        $modeloUsuario = new Usuario($conexao);

        $acao = (string)($_POST['action'] ?? '');
        $sucesso = false;
        $mensagem = 'Ação inválida.';

        if ($acao === 'create') {
            $sucesso = (bool)$modeloUsuario->post($_POST);
            $mensagem = $sucesso ? 'Usuário criado com sucesso.' : 'Erro ao criar usuário.';
        } elseif ($acao === 'update') {
            $id = (int)($_POST['id'] ?? 0);
            $sucesso = $id > 0 ? (bool)$modeloUsuario->put($id, $_POST) : false;
            $mensagem = $sucesso ? 'Usuário atualizado com sucesso.' : 'Erro ao atualizar usuário.';
        } elseif ($acao === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            $sucesso = $id > 0 ? (bool)$modeloUsuario->delete($id) : false;
            $mensagem = $sucesso ? 'Usuário removido com sucesso.' : 'Erro ao remover usuário.';
        }

        header('Location: ' . baseUrl('/usuarios') . '?ok=' . ($sucesso ? '1' : '0') . '&msg=' . rawurlencode($mensagem), true, 303);
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
