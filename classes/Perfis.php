<?php

class Perfis
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

        $modeloPerfil = new PerfilAutor($conexao);
        $modeloUsuario = new Usuario($conexao);

        $idEdicao = (int)($_GET['edit'] ?? 0);
        $perfilEdicao = $idEdicao > 0 ? $modeloPerfil->get($idEdicao) : null;

        $perfis = $modeloPerfil->get();
        $usuarios = $modeloUsuario->get();

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

        $acaoFormulario = $perfilEdicao
            ? Http::baseUrl('/perfis/' . (int)($perfilEdicao['id'] ?? 0))
            : Http::baseUrl('/perfis');

        Layout::topo('Perfis');

        require_once __DIR__ . '/../views/perfis.php';
        PerfisView::render([
            'perfilEdicao' => $perfilEdicao,
            'perfis' => $perfis,
            'usuarios' => $usuarios,
            'usuarioPorId' => $usuarioPorId,
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

        $modeloPerfil = new PerfilAutor($conexao);

        $dadosPost = Http::limparArray((array)$_POST);
        $sucesso = (bool)$modeloPerfil->post($dadosPost);
        $mensagem = $sucesso ? 'Perfil criado com sucesso.' : 'Erro ao criar perfil.';

        Http::setFlash($mensagem, $sucesso ? 'success' : 'danger');
        Http::redirect(Http::baseUrl('/perfis'), 303);
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
            $modeloPerfil = new PerfilAutor($conexao);

            $dadosPut = Http::lerDadosCorpoLimpo();

            $sucesso = (bool)$modeloPerfil->put($id, $dadosPut);
            $mensagem = $sucesso ? 'Perfil atualizado com sucesso.' : 'Erro ao atualizar perfil.';

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
            $modeloPerfil = new PerfilAutor($conexao);

            $sucesso = (bool)$modeloPerfil->delete($id);
            $mensagem = $sucesso ? 'Perfil removido com sucesso.' : 'Erro ao remover perfil.';

            Http::jsonResponse(['sucesso' => $sucesso, 'mensagem' => $mensagem], 200);
        } catch (Exception $e) {
            Http::jsonResponse(['sucesso' => false, 'mensagem' => 'Erro: ' . $e->getMessage()], 500);
        }
    }
}
