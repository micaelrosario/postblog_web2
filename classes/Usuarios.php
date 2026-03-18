<?php

class Usuarios
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

        $modeloUsuario = new Usuario($conexao);

        $idEdicao = (int)($_GET['edit'] ?? 0);
        $usuarioEdicao = $idEdicao > 0 ? $modeloUsuario->get($idEdicao) : null;

        $usuarios = $modeloUsuario->get();

        $acaoFormulario = $usuarioEdicao
            ? Http::baseUrl('/usuarios/' . (int)($usuarioEdicao['id'] ?? 0))
            : Http::baseUrl('/usuarios');

        Layout::topo('Usuários');

        require_once __DIR__ . '/../views/usuarios.php';
        UsuariosView::render([
            'usuarioEdicao' => $usuarioEdicao,
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

        $modeloUsuario = new Usuario($conexao);

        // Não aplica trim em "senha" para não alterar a senha digitada.
        $dadosPost = Http::limparArray((array)$_POST, ['naoTrim' => ['senha']]);
        $sucesso = (bool)$modeloUsuario->post($dadosPost);
        $mensagem = $sucesso ? 'Usuário criado com sucesso.' : 'Erro ao criar usuário.';

        Http::setFlash($mensagem, $sucesso ? 'success' : 'danger');
        Http::redirect(Http::baseUrl('/usuarios'), 303);
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
            $modeloUsuario = new Usuario($conexao);

            $dadosPut = Http::lerDadosCorpoLimpo(['naoTrim' => ['senha']]);

            $sucesso = (bool)$modeloUsuario->put($id, $dadosPut);
            $mensagem = $sucesso ? 'Usuário atualizado com sucesso.' : 'Erro ao atualizar usuário.';

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
            $modeloUsuario = new Usuario($conexao);

            $sucesso = (bool)$modeloUsuario->delete($id);
            $mensagem = $sucesso ? 'Usuário removido com sucesso.' : 'Erro ao remover usuário.';

            Http::jsonResponse(['sucesso' => $sucesso, 'mensagem' => $mensagem], 200);
        } catch (Exception $e) {
            Http::jsonResponse(['sucesso' => false, 'mensagem' => 'Erro: ' . $e->getMessage()], 500);
        }
    }
}
