<?php

declare(strict_types=1);

final class PostController
{
    private PostRepository $posts;

    public function __construct(PDO $db)
    {
        $this->posts = new PostRepository($db);
    }

    public function index(): void
    {
        headerHtml('Posts');

        echo "<h1 class='h3 mb-3'>Posts</h1>";

        try {
            $rows = $this->posts->all();
        } catch (Throwable $e) {
            http_response_code(500);
            echo "<div class='alert alert-danger'>Erro ao carregar os posts.</div>";
            echo "<pre class='small text-muted mb-0'>" . htmlspecialchars($e->getMessage(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "</pre>";
            footerHtml();
            return;
        }

        if (count($rows) === 0) {
            echo "<div class='alert alert-secondary'>Nenhum post foi encontrado.</div>";
            footerHtml();
            return;
        }

        echo "<div class='row row-cols-1 row-cols-md-2 g-3'>";

        foreach ($rows as $row) {
            $id = (int)($row['id'] ?? 0);
            $titulo = htmlspecialchars((string)($row['titulo'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $autor = htmlspecialchars($this->authorFromRow($row), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $imagem = trim((string)($row['imagem'] ?? ''));

            echo "<div class='col'>";
            echo "<div class='card h-100 overflow-hidden'>";

            // Imagem do card (altura fixa para não ocupar a tela)
            echo "  <div class='bg-body-tertiary' style='height: 180px;'>";
            if ($imagem !== '') {
                $imagemEsc = htmlspecialchars($imagem, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                echo "    <img class='w-100 h-100 object-fit-cover' src='{$imagemEsc}' alt='{$titulo}'>";
            } else {
                echo "    <div class='d-flex align-items-center justify-content-center text-secondary'>";
                echo $this->imagePlaceholderSvg();
                echo "    </div>";
            }
            echo "  </div>";

            echo "  <div class='card-body'>";
            echo "    <div class='fw-semibold'>";
            echo "      <a class='stretched-link text-decoration-none text-body' href='/posts/{$id}'>";
            echo "        {$titulo} - {$autor}";
            echo "      </a>";
            echo "    </div>";
            echo "  </div>";
            echo "</div>";
            echo "</div>";
        }

        echo "</div>";

        footerHtml();
    }

    public function create(): void
    {
        headerHtml('Novo post');

        echo "<h1 class='h3 mb-3'>Novo post</h1>";
        $this->renderForm('/posts', [
            'titulo' => '',
            'perfil_autor' => '',
            'conteudo' => '',
            'imagem' => '',
        ]);

        footerHtml();
    }

    /** @param array<string, mixed> $data */
    public function store(array $data): void
    {
        $titulo = trim((string)($data['titulo'] ?? ''));
        $perfilAutor = trim((string)($data['perfil_autor'] ?? ''));
        $conteudo = trim((string)($data['conteudo'] ?? ''));

        $errors = [];
        if ($titulo === '') {
            $errors[] = 'Título é obrigatório.';
        }
        if ($perfilAutor === '') {
            $errors[] = 'Autor é obrigatório.';
        }
        if ($conteudo === '') {
            $errors[] = 'Conteúdo é obrigatório.';
        }

        if (count($errors) > 0) {
            headerHtml('Novo post');
            echo "<div class='alert alert-danger'><ul class='m-0'>";
            foreach ($errors as $err) {
                echo '<li>' . htmlspecialchars($err, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</li>';
            }
            echo "</ul></div>";

            $this->renderForm('/posts', [
                'titulo' => $titulo,
                'perfil_autor' => $perfilAutor,
                'conteudo' => $conteudo,
                'imagem' => (string)($data['imagem'] ?? ''),
            ]);

            footerHtml();
            return;
        }

        $id = $this->posts->create([
            'titulo' => $titulo,
            'perfil_autor' => $perfilAutor,
            'conteudo' => $conteudo,
            'imagem' => (string)($data['imagem'] ?? ''),
        ]);

        header('Location: /posts/' . $id);
        exit;
    }

    public function show(int $id): void
    {
        $row = $this->posts->find($id);
        if ($row === null) {
            $this->notFound('Post não encontrado.');
            return;
        }

        $titulo = htmlspecialchars((string)($row['titulo'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $conteudo = nl2br(htmlspecialchars((string)($row['conteudo'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
        $criadoEm = htmlspecialchars((string)($row['criado_em'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $autor = htmlspecialchars($this->authorFromRow($row), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $imagem = trim((string)($row['imagem'] ?? ''));

        headerHtml($titulo);

        echo "<div class='d-flex justify-content-between align-items-start mb-3'>";
        echo "  <div>";
        echo "    <h1 class='h3 mb-1'>{$titulo}</h1>";
        echo "    <div class='text-muted'><small>{$autor}</small></div>";
        echo "    <div class='text-muted'><small>Publicado em: {$criadoEm}</small></div>";
        echo "  </div>";
        echo "  <div class='d-flex gap-2'>";
        echo "    <a class='btn btn-outline-secondary btn-sm' href='/posts/{$id}/editar'>Editar</a>";
        echo "    <a class='btn btn-outline-primary btn-sm' href='/posts'>Voltar</a>";
        echo "  </div>";
        echo "</div>";

        echo "<div class='card overflow-hidden'>";
        if ($imagem !== '') {
            $imagemEsc = htmlspecialchars($imagem, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            echo "  <img class='card-img-top' src='{$imagemEsc}' alt='{$titulo}'>";
        }
        echo "  <div class='card-body'>";
        echo "    <div class='card-text'>{$conteudo}</div>";
        echo "  </div>";
        echo "</div>";

        footerHtml();
    }

    public function edit(int $id): void
    {
        $row = $this->posts->find($id);
        if ($row === null) {
            $this->notFound('Post não encontrado.');
            return;
        }

        headerHtml('Editar post');
        echo "<h1 class='h3 mb-3'>Editar post</h1>";

        $this->renderForm('/posts/' . $id, [
            'titulo' => (string)($row['titulo'] ?? ''),
            'perfil_autor' => (string)($row['perfil_autor'] ?? ''),
            'conteudo' => (string)($row['conteudo'] ?? ''),
            'imagem' => (string)($row['imagem'] ?? ''),
        ]);

        footerHtml();
    }

    /** @param array<string, mixed> $data */
    public function update(int $id, array $data): void
    {
        $row = $this->posts->find($id);
        if ($row === null) {
            $this->notFound('Post não encontrado.');
            return;
        }

        $titulo = trim((string)($data['titulo'] ?? ''));
        $perfilAutor = trim((string)($data['perfil_autor'] ?? ''));
        $conteudo = trim((string)($data['conteudo'] ?? ''));

        $errors = [];
        if ($titulo === '') {
            $errors[] = 'Título é obrigatório.';
        }
        if ($perfilAutor === '') {
            $errors[] = 'Autor é obrigatório.';
        }
        if ($conteudo === '') {
            $errors[] = 'Conteúdo é obrigatório.';
        }

        if (count($errors) > 0) {
            headerHtml('Editar post');
            echo "<div class='alert alert-danger'><ul class='m-0'>";
            foreach ($errors as $err) {
                echo '<li>' . htmlspecialchars($err, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</li>';
            }
            echo "</ul></div>";

            $this->renderForm('/posts/' . $id, [
                'titulo' => $titulo,
                'perfil_autor' => $perfilAutor,
                'conteudo' => $conteudo,
                'imagem' => (string)($data['imagem'] ?? ''),
            ]);

            footerHtml();
            return;
        }

        $this->posts->update($id, [
            'titulo' => $titulo,
            'perfil_autor' => $perfilAutor,
            'conteudo' => $conteudo,
            'imagem' => (string)($data['imagem'] ?? ''),
        ]);

        header('Location: /posts/' . $id);
        exit;
    }

    public function destroy(int $id): void
    {
        $this->posts->delete($id);
        header('Location: /posts');
        exit;
    }

    /** @param array{titulo:string, perfil_autor:string, conteudo:string, imagem:string} $values */
    private function renderForm(string $action, array $values): void
    {
        $titulo = htmlspecialchars($values['titulo'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $perfilAutor = htmlspecialchars($values['perfil_autor'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $conteudo = htmlspecialchars($values['conteudo'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $imagem = htmlspecialchars($values['imagem'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        echo "<form method='post' action='" . htmlspecialchars($action, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "'>";
        echo "  <div class='mb-3'>";
        echo "    <label class='form-label' for='titulo'>Título</label>";
        echo "    <input class='form-control' id='titulo' name='titulo' value='{$titulo}' required>";
        echo "  </div>";
        echo "  <div class='mb-3'>";
        echo "    <label class='form-label' for='perfil_autor'>Autor</label>";
        echo "    <input class='form-control' id='perfil_autor' name='perfil_autor' value='{$perfilAutor}' required>";
        echo "  </div>";
        echo "  <div class='mb-3'>";
        echo "    <label class='form-label' for='conteudo'>Conteúdo</label>";
        echo "    <textarea class='form-control' id='conteudo' name='conteudo' rows='6' required>{$conteudo}</textarea>";
        echo "  </div>";
        echo "  <div class='mb-3'>";
        echo "    <label class='form-label' for='imagem'>Imagem (URL ou caminho)</label>";
        echo "    <input class='form-control' id='imagem' name='imagem' value='{$imagem}'>";
        echo "  </div>";
        echo "  <div class='d-flex gap-2'>";
        echo "    <button class='btn btn-primary' type='submit'>Salvar</button>";
        echo "    <a class='btn btn-outline-secondary' href='/posts'>Cancelar</a>";
        echo "  </div>";
        echo "</form>";
    }

    private function notFound(string $message): void
    {
        http_response_code(404);
        headerHtml('404');
        echo "<div class='alert alert-warning mb-3'>" . htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "</div>";
        echo "<a class='btn btn-outline-primary' href='/posts'>Voltar para posts</a>";
        footerHtml();
    }

    /** @param array<string, mixed> $row */
    private function authorFromRow(array $row): string
    {
        $candidateKeys = [
            'perfil_autor',
            'autor',
            'autor_nome',
            'nome_autor',
            'author',
            'author_name',
        ];

        $normalizedCandidates = array_fill_keys($candidateKeys, true);

        foreach ($row as $key => $value) {
            $normalizedKey = strtolower((string)$key);
            if (!isset($normalizedCandidates[$normalizedKey])) {
                continue;
            }

            $name = trim((string)$value);
            if ($name !== '') {
                return $name;
            }
        }

        return 'Autor desconhecido';
    }

    private function imagePlaceholderSvg(): string
    {
        return "<svg xmlns='http://www.w3.org/2000/svg' width='56' height='56' viewBox='0 0 64 64' role='img' aria-label='Sem imagem'>"
            . "<rect x='10' y='14' width='44' height='36' rx='4' ry='4' fill='none' stroke='currentColor' stroke-width='4'/>"
            . "<circle cx='26' cy='28' r='4' fill='currentColor'/>"
            . "<path d='M18 46l10-12 8 10 6-8 12 10' fill='none' stroke='currentColor' stroke-width='4' stroke-linecap='round' stroke-linejoin='round'/>"
            . "</svg>";
    }
}
