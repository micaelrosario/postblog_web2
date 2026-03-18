<?php

final class PostsView
{
    public static function render(array $dados): void
    {
        $postEdicao = $dados['postEdicao'] ?? null;
        $usuarioAutenticado = (bool)($dados['usuarioAutenticado'] ?? false);
        $usuarios = $dados['usuarios'] ?? [];
        $categorias = $dados['categorias'] ?? [];
        $posts = $dados['posts'] ?? [];
        $usuarioPorId = $dados['usuarioPorId'] ?? [];
        $categoriaPorId = $dados['categoriaPorId'] ?? [];
        $acaoFormulario = (string)($dados['acaoFormulario'] ?? Http::baseUrl('/posts'));
        $urlLeitor = (string)($dados['urlLeitor'] ?? Http::baseUrl('/posts'));
        $urlAdmin = (string)($dados['urlAdmin'] ?? Http::baseUrl('/adicionar-posts'));
        $urlRetorno = $usuarioAutenticado ? $urlAdmin : $urlLeitor;

        ?>

        <h1 class="h3 mb-3">Posts</h1>

        <div class="row g-4">
            <?php if ($usuarioAutenticado) { ?>
                <div class="col-lg-5">
                    <div class="card">
                        <div class="card-body">
                            <h2 class="h5 mb-3"><?php echo $postEdicao ? 'Editar Post' : 'Novo Post'; ?></h2>

                            <form method="post"
                                action="<?php echo Http::e($acaoFormulario); ?>"
                                <?php if ($postEdicao) { ?>data-metodo-rest="PUT" data-redirecionar="<?php echo Http::e($urlRetorno); ?>"<?php } ?>
                            >

                                <div class="mb-3">
                                    <label class="form-label" for="titulo">Título</label>
                                    <input class="form-control" id="titulo" name="titulo" required value="<?php echo Http::e($postEdicao['titulo'] ?? ''); ?>">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="autor_id">Autor</label>
                                    <select class="form-select" id="autor_id" name="autor_id" required>
                                        <?php foreach ($usuarios as $usuario) {
                                            $selecionado = ($postEdicao && (int)($postEdicao['autor_id'] ?? 0) === (int)($usuario['id'] ?? 0)) ? 'selected' : '';
                                            $nomeExibicao = $usuario['username'] ?? ($usuario['first_name'] ?? ('Usuário #' . ($usuario['id'] ?? '')));
                                        ?>
                                            <option value="<?php echo Http::e($usuario['id'] ?? ''); ?>" <?php echo $selecionado; ?>><?php echo Http::e($nomeExibicao); ?></option>
                                        <?php } ?>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="categoria_id">Categoria</label>
                                    <select class="form-select" id="categoria_id" name="categoria_id" required>
                                        <?php foreach ($categorias as $categoria) {
                                            $selecionado = ($postEdicao && (int)($postEdicao['categoria_id'] ?? 0) === (int)($categoria['id'] ?? 0)) ? 'selected' : '';
                                        ?>
                                            <option value="<?php echo Http::e($categoria['id'] ?? ''); ?>" <?php echo $selecionado; ?>><?php echo Http::e($categoria['nome'] ?? ''); ?></option>
                                        <?php } ?>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="conteudo">Conteúdo</label>
                                    <textarea class="form-control" id="conteudo" name="conteudo" rows="5" required><?php echo Http::e($postEdicao['conteudo'] ?? ''); ?></textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="imagem">Imagem (URL)</label>
                                    <input class="form-control" id="imagem" name="imagem" value="<?php echo Http::e($postEdicao['imagem'] ?? ''); ?>">
                                    <div class="form-text">Cole um link direto (http/https) ou um caminho de arquivo dentro do projeto (ex.: <span class="font-monospace">uploads/capa.jpg</span>).</div>
                                </div>

                                <div class="d-flex gap-2">
                                    <button class="btn btn-primary" type="submit">Salvar</button>
                                    <?php if ($postEdicao) { ?>
                                        <a class="btn btn-outline-secondary" href="<?php echo Http::e($urlRetorno); ?>">Cancelar</a>
                                    <?php } ?>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php } ?>

            <div class="<?php echo $usuarioAutenticado ? 'col-lg-7' : 'col-lg-12'; ?>">
                <h2 class="h5 mb-3">Lista</h2>

                <?php if (count($posts) === 0) { ?>
                    <div class="alert alert-light border text-muted mb-0">Nenhum post encontrado.</div>
                <?php } else { ?>
                    <div class="vstack gap-3">
                        <?php foreach ($posts as $post) {
                            $titulo = (string)($post['titulo'] ?? '');
                            $imagem = trim((string)($post['imagem'] ?? ''));
                            if ($imagem === '[object File]') {
                                $imagem = '';
                            }
                            $imagem = str_replace('\\', '/', $imagem);

                            $imagemSrc = '';
                            if ($imagem !== '') {
                                $base = rtrim(Http::baseUrl('/'), '/');

                                $ehAbsoluta = (preg_match('/^[a-zA-Z][a-zA-Z0-9+.-]*:/', $imagem) === 1)
                                    || str_starts_with($imagem, '//');

                                if ($ehAbsoluta) {
                                    $imagemSrc = $imagem;
                                } elseif ($imagem[0] === '/') {
                                    $imagemSrc = ($base !== '' && !str_starts_with($imagem, $base . '/'))
                                        ? ($base . $imagem)
                                        : $imagem;
                                } else {
                                    $imagemSrc = ($base !== '' ? $base : '') . '/' . ltrim($imagem, '/');
                                }
                            }

                            $criadoEm = (string)($post['criado_em'] ?? '');
                            $data = $criadoEm !== '' ? date('d/m/Y', strtotime($criadoEm)) : '—';

                            $autorId = (int)($post['autor_id'] ?? 0);
                            $autorNome = $autorId > 0
                                ? ((string)($usuarioPorId[$autorId] ?? ('Usuário #' . $autorId)))
                                : '—';

                            $conteudo = (string)($post['conteudo'] ?? '');
                            $conteudoLimpo = trim(preg_replace('/\s+/', ' ', strip_tags($conteudo)) ?? '');

                            $descricao = $conteudoLimpo !== '' ? $conteudoLimpo : '—';
                            $maxDescricao = 160;
                            if (function_exists('mb_strlen') && function_exists('mb_substr')) {
                                if (mb_strlen($descricao, 'UTF-8') > $maxDescricao) {
                                    $descricao = mb_substr($descricao, 0, $maxDescricao, 'UTF-8') . '...';
                                }
                            } else {
                                if (strlen($descricao) > $maxDescricao) {
                                    $descricao = substr($descricao, 0, $maxDescricao) . '...';
                                }
                            }
                        ?>
                            <div class="card overflow-hidden">
                                <div class="row g-0">
                                    <div class="col-md-4">
                                        <?php if ($imagemSrc !== '') { ?>
                                            <img
                                                src="<?php echo Http::e($imagemSrc); ?>"
                                                alt="<?php echo Http::e($titulo); ?>"
                                                class="img-fluid h-100 w-100"
                                                style="object-fit: cover;"
                                            >
                                        <?php } else { ?>
                                            <div class="h-100 w-100 bg-body-tertiary d-flex align-items-center justify-content-center text-body-secondary p-4 text-center">
                                                Sem imagem
                                            </div>
                                        <?php } ?>
                                    </div>

                                    <div class="col-md-8">
                                        <div class="card-body">
                                            <h3 class="h5 card-title mb-1"><?php echo Http::e($titulo); ?></h3>
                                            <p class="card-text mb-2"><small class="text-body-secondary">Por <?php echo Http::e($autorNome); ?> • <?php echo Http::e($data); ?></small></p>
                                            <p class="card-text mb-0"><?php echo Http::e($descricao); ?></p>
                                        </div>

                                        <?php if ($usuarioAutenticado) { ?>
                                            <div class="card-footer bg-transparent d-flex justify-content-end gap-2">
                                                <a class="btn btn-sm btn-outline-secondary" href="<?php echo Http::e($urlAdmin . '?edit=' . (int)($post['id'] ?? 0)); ?>">Editar</a>

                                                <form method="post" action="<?php echo Http::e(Http::baseUrl('/posts/' . (int)($post['id'] ?? 0))); ?>" class="m-0" data-metodo-rest="DELETE" data-redirecionar="<?php echo Http::e($urlRetorno); ?>" onsubmit="return confirm('Remover este post?');">
                                                    <button class="btn btn-sm btn-outline-danger" type="submit">Excluir</button>
                                                </form>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                <?php } ?>
            </div>
        </div>
        <?php
    }
}
