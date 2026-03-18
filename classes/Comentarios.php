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
            Layout::topo('Erro');
            echo '<div class="alert alert-danger">Falha ao conectar no banco de dados.</div>';
            echo '<pre class="small text-muted mb-0">' . Http::e($e->getMessage()) . '</pre>';
            Layout::rodape();
            return;
        }

        $modeloComentario = new Comentario($conexao);
        $modeloPost = new Post($conexao);
        $modeloUsuario = new Usuario($conexao);

        $idEdicao = (int)($_GET['edit'] ?? 0);
        $comentarioEdicao = $idEdicao > 0 ? $modeloComentario->get($idEdicao) : null;

        $comentarios = $modeloComentario->get();
        $posts = $modeloPost->get();
        $usuarios = $modeloUsuario->get();

        $acaoFormulario = $comentarioEdicao
            ? Http::baseUrl('/comentarios/' . (int)($comentarioEdicao['id'] ?? 0))
            : Http::baseUrl('/comentarios');

        Layout::topo('Comentários');

        $mensagem = (string)($_GET['msg'] ?? '');
        if ($mensagem !== '') {
            $okUrl = (string)($_GET['ok'] ?? '0');
            $tipo = $okUrl === '1' ? 'success' : 'danger';
            echo '<div class="alert alert-' . Http::e($tipo) . '">' . Http::e($mensagem) . '</div>';
        }

        require_once __DIR__ . '/../views/comentarios.php';
        ComentariosView::render([
            'comentarioEdicao' => $comentarioEdicao,
            'comentarios' => $comentarios,
            'posts' => $posts,
            'usuarios' => $usuarios,
            'acaoFormulario' => $acaoFormulario,
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

        $modeloComentario = new Comentario($conexao);

        $dadosPost = Http::limparArray((array)$_POST);
        $sucesso = (bool)$modeloComentario->post($dadosPost);
        $mensagem = $sucesso ? 'Comentário criado com sucesso.' : 'Erro ao criar comentário.';

        Http::redirect(Http::baseUrl('/comentarios') . '?ok=' . ($sucesso ? '1' : '0') . '&msg=' . rawurlencode($mensagem), 303);
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
            $modeloComentario = new Comentario($conexao);

            $dadosPut = Http::lerDadosCorpoLimpo();

            $sucesso = (bool)$modeloComentario->put($id, $dadosPut);
            $mensagem = $sucesso ? 'Comentário atualizado com sucesso.' : 'Erro ao atualizar comentário.';

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
            $modeloComentario = new Comentario($conexao);

            $sucesso = (bool)$modeloComentario->delete($id);
            $mensagem = $sucesso ? 'Comentário removido com sucesso.' : 'Erro ao remover comentário.';

            Http::jsonResponse(['sucesso' => $sucesso, 'mensagem' => $mensagem], 200);
        } catch (Exception $e) {
            Http::jsonResponse(['sucesso' => false, 'mensagem' => 'Erro: ' . $e->getMessage()], 500);
        }
    }
}
