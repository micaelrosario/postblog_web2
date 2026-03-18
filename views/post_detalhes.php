<?php

final class PostDetalhesView
{
    private static function imagemSrc(string $imagem): string
    {
        $imagem = trim($imagem);
        if ($imagem === '' || $imagem === '[object File]') {
            return '';
        }

        $imagem = str_replace('\\', '/', $imagem);

        $base = rtrim(Http::baseUrl('/'), '/');

        $ehAbsoluta = (preg_match('/^[a-zA-Z][a-zA-Z0-9+.-]*:/', $imagem) === 1)
            || str_starts_with($imagem, '//');

        if ($ehAbsoluta) {
            return $imagem;
        }

        if ($imagem[0] === '/') {
            return ($base !== '' && !str_starts_with($imagem, $base . '/'))
                ? ($base . $imagem)
                : $imagem;
        }

        return ($base !== '' ? $base : '') . '/' . ltrim($imagem, '/');
    }

    public static function render(array $dados): void
    {
        $post = $dados['post'] ?? null;
        if (!is_array($post) || empty($post)) {
            echo '<div class="alert alert-warning">Post não encontrado.</div>';
            return;
        }

        $postId = (int)($post['id'] ?? 0);
        $titulo = (string)($post['titulo'] ?? '');
        $conteudo = (string)($post['conteudo'] ?? '');

        $usuarioPorId = (array)($dados['usuarioPorId'] ?? []);
        $categoriaPorId = (array)($dados['categoriaPorId'] ?? []);
        $comentarios = (array)($dados['comentarios'] ?? []);

        $usuarioAutenticado = (bool)($dados['usuarioAutenticado'] ?? false);

        $urlVoltar = (string)($dados['urlVoltar'] ?? Http::baseUrl('/posts'));
        $urlAdicionarComentario = (string)($dados['urlAdicionarComentario'] ?? Http::baseUrl('/posts/' . $postId . '/comentarios'));
        $urlLogin = (string)($dados['urlLogin'] ?? Http::baseUrl('/login'));

        $criadoEm = (string)($post['criado_em'] ?? '');
        $dataHora = $criadoEm !== '' ? date('d/m/Y H:i', strtotime($criadoEm)) : '—';

        $autorId = (int)($post['autor_id'] ?? 0);
        $autorNome = $autorId > 0
            ? ((string)($usuarioPorId[$autorId] ?? ('Usuário #' . $autorId)))
            : '—';

        $categoriaId = (int)($post['categoria_id'] ?? 0);
        $categoriaNome = $categoriaId > 0
            ? ((string)($categoriaPorId[$categoriaId] ?? ('Categoria #' . $categoriaId)))
            : '—';

        $imagemSrc = self::imagemSrc((string)($post['imagem'] ?? ''));
        $qtdComentarios = count($comentarios);

        ?>

        <div class="d-flex align-items-center justify-content-between mb-3">
            <a class="btn btn-sm btn-outline-secondary" href="<?php echo Http::e($urlVoltar); ?>">&larr; Voltar</a>
        </div>

        <article class="card">
            <div class="card-body">
                <header class="mb-3">
                    <h1 class="h3 mb-2"><?php echo Http::e($titulo !== '' ? $titulo : ('Post #' . $postId)); ?></h1>
                    <div class="text-body-secondary small">
                        Por <?php echo Http::e($autorNome); ?>
                        &bull; <?php echo Http::e($dataHora); ?>
                        &bull; <?php echo Http::e($categoriaNome); ?>
                    </div>
                </header>

                <?php if ($imagemSrc !== '') { ?>
                    <div class="mb-4 text-center">
                        <img
                            src="<?php echo Http::e($imagemSrc); ?>"
                            alt="<?php echo Http::e($titulo); ?>"
                            class="img-fluid rounded mx-auto d-block"
                            style="max-height: 480px; object-fit: contain;"
                        >
                    </div>
                <?php } ?>

                <div class="fs-6 lh-lg">
                    <?php echo nl2br(Http::e($conteudo)); ?>
                </div>
            </div>
        </article>

        <section class="mt-4">
            <h2 class="h5 mb-3">Comentários (<?php echo (int)$qtdComentarios; ?>)</h2>

            <?php if ($qtdComentarios === 0) { ?>
                <div class="alert alert-light border text-muted">Ainda não há comentários neste post.</div>
            <?php } else { ?>
                <div class="vstack gap-3">
                    <?php foreach ($comentarios as $comentario) {
                        $comentAutorId = (int)($comentario['autor_id'] ?? 0);
                        $comentAutorNome = $comentAutorId > 0
                            ? ((string)($usuarioPorId[$comentAutorId] ?? ('Usuário #' . $comentAutorId)))
                            : '—';

                        $comentCriadoEm = (string)($comentario['criado_em'] ?? '');
                        $comentDataHora = $comentCriadoEm !== '' ? date('d/m/Y H:i', strtotime($comentCriadoEm)) : '—';

                        $texto = (string)($comentario['texto'] ?? '');
                    ?>
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start gap-3">
                                    <div>
                                        <div class="fw-semibold"><?php echo Http::e($comentAutorNome); ?></div>
                                        <div class="text-body-secondary small"><?php echo Http::e($comentDataHora); ?></div>
                                    </div>
                                </div>

                                <hr class="my-3">

                                <p class="mb-0"><?php echo nl2br(Http::e($texto)); ?></p>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>
        </section>

        <section class="mt-4">
            <h2 class="h5 mb-3">Adicionar comentário</h2>

            <?php if (!$usuarioAutenticado) { ?>
                <div class="alert alert-warning">
                    Você precisa estar logado para comentar.
                    <a href="<?php echo Http::e($urlLogin); ?>">Entrar</a>
                </div>
            <?php } else { ?>
                <div class="card">
                    <div class="card-body">
                        <form method="post" action="<?php echo Http::e($urlAdicionarComentario); ?>">
                            <?php echo Http::csrfField(); ?>
                            <div class="mb-3">
                                <label class="form-label" for="texto">Comentário</label>
                                <textarea class="form-control" id="texto" name="texto" rows="4" required></textarea>
                            </div>

                            <button class="btn btn-primary" type="submit">Publicar comentário</button>
                        </form>
                    </div>
                </div>
            <?php } ?>
        </section>

        <?php
    }
}
