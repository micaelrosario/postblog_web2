<?php

class Categorias
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

        $modeloCategoria = new Categoria($conexao);

        $idEdicao = (int)($_GET['edit'] ?? 0);
        $categoriaEdicao = $idEdicao > 0 ? $modeloCategoria->get($idEdicao) : null;

        $categorias = $modeloCategoria->get();

        $acaoFormulario = $categoriaEdicao
            ? Http::baseUrl('/categorias/' . (int)($categoriaEdicao['id'] ?? 0))
            : Http::baseUrl('/categorias');

        Layout::topo('Categorias');

        $mensagem = (string)($_GET['msg'] ?? '');
        if ($mensagem !== '') {
            $okUrl = (string)($_GET['ok'] ?? '0');
            $tipo = $okUrl === '1' ? 'success' : 'danger';
            echo '<div class="alert alert-' . Http::e($tipo) . '">' . Http::e($mensagem) . '</div>';
        }

        require_once __DIR__ . '/../views/categorias.php';
        CategoriasView::render([
            'categoriaEdicao' => $categoriaEdicao,
            'categorias' => $categorias,
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

        $modeloCategoria = new Categoria($conexao);

        $dadosPost = Http::limparArray((array)$_POST);
        $sucesso = (bool)$modeloCategoria->post($dadosPost);
        $mensagem = $sucesso ? 'Categoria criada com sucesso.' : 'Erro ao criar categoria.';

        Http::redirect(Http::baseUrl('/categorias') . '?ok=' . ($sucesso ? '1' : '0') . '&msg=' . rawurlencode($mensagem), 303);
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
            $modeloCategoria = new Categoria($conexao);

            $dadosPut = Http::lerDadosCorpoLimpo();

            $sucesso = (bool)$modeloCategoria->put($id, $dadosPut);
            $mensagem = $sucesso ? 'Categoria atualizada com sucesso.' : 'Erro ao atualizar categoria.';

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
            $modeloCategoria = new Categoria($conexao);

            $sucesso = (bool)$modeloCategoria->delete($id);
            $mensagem = $sucesso ? 'Categoria removida com sucesso.' : 'Erro ao remover categoria.';

            Http::jsonResponse(['sucesso' => $sucesso, 'mensagem' => $mensagem], 200);
        } catch (Exception $e) {
            Http::jsonResponse(['sucesso' => false, 'mensagem' => 'Erro: ' . $e->getMessage()], 500);
        }
    }
}
