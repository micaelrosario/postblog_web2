<?php

class Categorias
{
    private function obterId(array $segmentosUrl): int
    {
        if (isset($segmentosUrl[1]) && ctype_digit((string)$segmentosUrl[1])) {
            return (int)$segmentosUrl[1];
        }

        return 0;
    }

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

    public function get(array $segmentosUrl): void
    {
        $conexao = $this->conectarOuRenderErro();
        if (!$conexao) {
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

        require_once __DIR__ . '/../views/categorias.php';
        CategoriasView::render([
            'categoriaEdicao' => $categoriaEdicao,
            'categorias' => $categorias,
            'acaoFormulario' => $acaoFormulario,
        ]);

        Layout::rodape();
    }

    public function post(array $segmentosUrl): void
    {
        $id = $this->obterId($segmentosUrl);
        if ($id > 0) {
            http_response_code(405);
            echo 'Método HTTP não suportado.';
            return;
        }

        $conexao = $this->conectarOuRenderErro();
        if (!$conexao) {
            return;
        }

        $modeloCategoria = new Categoria($conexao);

        $dadosPost = Http::limparArray((array)$_POST);
        $sucesso = (bool)$modeloCategoria->post($dadosPost);
        $mensagem = $sucesso ? 'Categoria criada com sucesso.' : 'Erro ao criar categoria.';

        Http::setFlash($mensagem, $sucesso ? 'success' : 'danger');
        Http::redirect(Http::baseUrl('/categorias'), 303);
        exit;
    }

    public function put(array $segmentosUrl): void
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

    public function delete(array $segmentosUrl): void
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
