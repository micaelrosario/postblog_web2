<?php

declare(strict_types=1);

final class CategoryController
{
    private CategoryRepository $categories;

    public function __construct(PDO $db)
    {
        $this->categories = new CategoryRepository($db);
    }

    public function index(): void
    {
        headerHtml('Categorias');

        echo "<div class='d-flex justify-content-between align-items-center mb-3'>";
        echo "  <h1 class='h3 m-0'>Categorias</h1>";
        echo "  <a class='btn btn-primary' href='/categorias/nova'>Nova categoria</a>";
        echo "</div>";

        try {
            $rows = $this->categories->all();
        } catch (Throwable $e) {
            http_response_code(500);
            echo "<div class='alert alert-danger'>Erro ao carregar categorias.</div>";
            echo "<pre class='small text-muted mb-0'>" . htmlspecialchars($e->getMessage(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "</pre>";
            footerHtml();
            return;
        }

        if (count($rows) === 0) {
            echo "<div class='alert alert-secondary'>Nenhuma categoria foi encontrada.</div>";
            footerHtml();
            return;
        }

        echo "<div class='list-group'>";
        foreach ($rows as $row) {
            $id = (int)($row['id'] ?? 0);
            $nome = htmlspecialchars((string)($row['nome'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

            echo "<div class='list-group-item d-flex justify-content-between align-items-center gap-2'>";
            echo "  <div class='fw-semibold'>{$nome}</div>";
            echo "  <div class='d-flex gap-2'>";
            echo "    <a class='btn btn-outline-secondary btn-sm' href='/categorias/{$id}/editar'>Editar</a>";
            echo "    <form method='post' action='/categorias/{$id}/excluir' onsubmit='return confirm(\"Excluir esta categoria?\")'>";
            echo "      <button class='btn btn-outline-danger btn-sm' type='submit'>Excluir</button>";
            echo "    </form>";
            echo "  </div>";
            echo "</div>";
        }
        echo "</div>";

        footerHtml();
    }

    public function create(): void
    {
        headerHtml('Nova categoria');

        echo "<h1 class='h3 mb-3'>Nova categoria</h1>";
        $this->renderForm('/categorias', ['nome' => '']);

        footerHtml();
    }

    /** @param array<string, mixed> $data */
    public function store(array $data): void
    {
        $nome = trim((string)($data['nome'] ?? ''));

        if ($nome === '') {
            headerHtml('Nova categoria');
            echo "<div class='alert alert-danger'>Nome é obrigatório.</div>";
            $this->renderForm('/categorias', ['nome' => $nome]);
            footerHtml();
            return;
        }

        try {
            $this->categories->create($nome);
        } catch (Throwable $e) {
            http_response_code(500);
            headerHtml('Nova categoria');
            echo "<div class='alert alert-danger'>Erro ao criar categoria.</div>";
            echo "<pre class='small text-muted mb-0'>" . htmlspecialchars($e->getMessage(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "</pre>";
            footerHtml();
            return;
        }

        header('Location: /categorias');
        exit;
    }

    public function edit(int $id): void
    {
        try {
            $row = $this->categories->find($id);
        } catch (Throwable $e) {
            http_response_code(500);
            headerHtml('Editar categoria');
            echo "<div class='alert alert-danger'>Erro ao carregar categoria.</div>";
            echo "<pre class='small text-muted mb-0'>" . htmlspecialchars($e->getMessage(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "</pre>";
            footerHtml();
            return;
        }

        if ($row === null) {
            $this->notFound('Categoria não encontrada.');
            return;
        }

        headerHtml('Editar categoria');
        echo "<h1 class='h3 mb-3'>Editar categoria</h1>";

        $this->renderForm('/categorias/' . $id, [
            'nome' => (string)($row['nome'] ?? ''),
        ]);

        footerHtml();
    }

    /** @param array<string, mixed> $data */
    public function update(int $id, array $data): void
    {
        $nome = trim((string)($data['nome'] ?? ''));

        if ($nome === '') {
            headerHtml('Editar categoria');
            echo "<div class='alert alert-danger'>Nome é obrigatório.</div>";
            $this->renderForm('/categorias/' . $id, ['nome' => $nome]);
            footerHtml();
            return;
        }

        try {
            $ok = $this->categories->update($id, $nome);
        } catch (Throwable $e) {
            http_response_code(500);
            headerHtml('Editar categoria');
            echo "<div class='alert alert-danger'>Erro ao salvar categoria.</div>";
            echo "<pre class='small text-muted mb-0'>" . htmlspecialchars($e->getMessage(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "</pre>";
            footerHtml();
            return;
        }

        if (!$ok) {
            http_response_code(500);
            headerHtml('Editar categoria');
            echo "<div class='alert alert-danger'>Não foi possível salvar.</div>";
            footerHtml();
            return;
        }

        header('Location: /categorias');
        exit;
    }

    public function destroy(int $id): void
    {
        try {
            $this->categories->delete($id);
        } catch (Throwable $e) {
            http_response_code(500);
            headerHtml('Categorias');
            echo "<div class='alert alert-danger'>Erro ao excluir categoria.</div>";
            echo "<pre class='small text-muted mb-0'>" . htmlspecialchars($e->getMessage(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "</pre>";
            footerHtml();
            return;
        }

        header('Location: /categorias');
        exit;
    }

    /** @param array{nome:string} $values */
    private function renderForm(string $action, array $values): void
    {
        $nome = htmlspecialchars($values['nome'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        echo "<form method='post' action='" . htmlspecialchars($action, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "'>";
        echo "  <div class='mb-3'>";
        echo "    <label class='form-label' for='nome'>Nome</label>";
        echo "    <input class='form-control' id='nome' name='nome' value='{$nome}' required>";
        echo "  </div>";
        echo "  <div class='d-flex gap-2'>";
        echo "    <button class='btn btn-primary' type='submit'>Salvar</button>";
        echo "    <a class='btn btn-outline-secondary' href='/categorias'>Cancelar</a>";
        echo "  </div>";
        echo "</form>";
    }

    private function notFound(string $message): void
    {
        http_response_code(404);
        headerHtml('404');
        echo "<div class='alert alert-warning mb-3'>" . htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "</div>";
        echo "<a class='btn btn-outline-primary' href='/categorias'>Voltar para categorias</a>";
        footerHtml();
    }
}
