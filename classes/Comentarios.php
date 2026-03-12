<?php

class Comentarios
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
            topo('Erro');
            echo '<div class="alert alert-danger">Falha ao conectar no banco de dados.</div>';
            echo '<pre class="small text-muted mb-0">' . e($e->getMessage()) . '</pre>';
            rodape();
            return;
        }

        $modeloComentario = new Comentario($conexao);
        $modeloPost = new Post($conexao);
        $modeloUsuario = new Usuario($conexao);

        topo('Comentários');

        $mensagem = (string)($_GET['msg'] ?? '');
        if ($mensagem !== '') {
            $okUrl = (string)($_GET['ok'] ?? '0');
            $tipo = $okUrl === '1' ? 'success' : 'danger';
            echo '<div class="alert alert-' . e($tipo) . '">' . e($mensagem) . '</div>';
        }

        require __DIR__ . '/../views/comentarios.php';

        rodape();
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
            topo('Erro');
            echo '<div class="alert alert-danger">Falha ao conectar no banco de dados.</div>';
            echo '<pre class="small text-muted mb-0">' . e($e->getMessage()) . '</pre>';
            rodape();
            return;
        }

        $modeloComentario = new Comentario($conexao);

        $sucesso = (bool)$modeloComentario->post($_POST);
        $mensagem = $sucesso ? 'Comentário criado com sucesso.' : 'Erro ao criar comentário.';

        header('Location: ' . baseUrl('/comentarios') . '?ok=' . ($sucesso ? '1' : '0') . '&msg=' . rawurlencode($mensagem), true, 303);
        exit;
    }

    public function put($segmentosUrl)
    {
        $id = $this->obterId($segmentosUrl);
        if ($id <= 0) {
            jsonResponse(['sucesso' => false, 'mensagem' => 'Parâmetro id é obrigatório.'], 400);
            return;
        }

        try {
            $conexao = $this->conectar();
            $modeloComentario = new Comentario($conexao);

            $dadosPut = lerDadosCorpo();

            $sucesso = (bool)$modeloComentario->put($id, $dadosPut);
            $mensagem = $sucesso ? 'Comentário atualizado com sucesso.' : 'Erro ao atualizar comentário.';

            jsonResponse(['sucesso' => $sucesso, 'mensagem' => $mensagem], 200);
        } catch (Exception $e) {
            jsonResponse(['sucesso' => false, 'mensagem' => 'Erro: ' . $e->getMessage()], 500);
        }
    }

    public function delete($segmentosUrl)
    {
        $id = $this->obterId($segmentosUrl);
        if ($id <= 0) {
            jsonResponse(['sucesso' => false, 'mensagem' => 'Parâmetro id é obrigatório.'], 400);
            return;
        }

        try {
            $conexao = $this->conectar();
            $modeloComentario = new Comentario($conexao);

            $sucesso = (bool)$modeloComentario->delete($id);
            $mensagem = $sucesso ? 'Comentário removido com sucesso.' : 'Erro ao remover comentário.';

            jsonResponse(['sucesso' => $sucesso, 'mensagem' => $mensagem], 200);
        } catch (Exception $e) {
            jsonResponse(['sucesso' => false, 'mensagem' => 'Erro: ' . $e->getMessage()], 500);
        }
    }
}
