<?php

defined('ACCESS') or die('Acesso negado');

$editId = (int)($_GET['edit'] ?? 0);
$editPost = $editId > 0 ? $postModel->get($editId) : null;

$usuarios = $usuarioModel->get();
$categorias = $categoriaModel->get();
$posts = $postModel->get();

?>

<h1 class="h3 mb-3">Posts</h1>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card">
            <div class="card-body">
                <h2 class="h5 mb-3"><?php echo $editPost ? 'Editar Post' : 'Novo Post'; ?></h2>

                <form method="post" action="<?php echo e(baseUrl('/posts')); ?>">
                    <input type="hidden" name="action" value="<?php echo $editPost ? 'update' : 'create'; ?>">
                    <?php if ($editPost) { ?>
                        <input type="hidden" name="id" value="<?php echo e($editPost['id']); ?>">
                    <?php } ?>

                    <div class="mb-3">
                        <label class="form-label" for="titulo">Título</label>
                        <input class="form-control" id="titulo" name="titulo" required value="<?php echo e($editPost['titulo'] ?? ''); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="autor_id">Autor</label>
                        <select class="form-select" id="autor_id" name="autor_id" required>
                            <?php foreach ($usuarios as $u) {
                                $selected = ($editPost && (int)($editPost['autor_id'] ?? 0) === (int)$u['id']) ? 'selected' : '';
                                $label = $u['username'] ?? ($u['first_name'] ?? ('Usuário #' . $u['id']));
                            ?>
                                <option value="<?php echo e($u['id']); ?>" <?php echo $selected; ?>><?php echo e($label); ?></option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="categoria_id">Categoria</label>
                        <select class="form-select" id="categoria_id" name="categoria_id" required>
                            <?php foreach ($categorias as $c) {
                                $selected = ($editPost && (int)($editPost['categoria_id'] ?? 0) === (int)$c['id']) ? 'selected' : '';
                            ?>
                                <option value="<?php echo e($c['id']); ?>" <?php echo $selected; ?>><?php echo e($c['nome']); ?></option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="conteudo">Conteúdo</label>
                        <textarea class="form-control" id="conteudo" name="conteudo" rows="5" required><?php echo e($editPost['conteudo'] ?? ''); ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="imagem">Imagem (URL)</label>
                        <input class="form-control" id="imagem" name="imagem" value="<?php echo e($editPost['imagem'] ?? ''); ?>">
                    </div>

                    <div class="d-flex gap-2">
                        <button class="btn btn-primary" type="submit">Salvar</button>
                        <?php if ($editPost) { ?>
                            <a class="btn btn-outline-secondary" href="<?php echo e(baseUrl('/posts')); ?>">Cancelar</a>
                        <?php } ?>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card">
            <div class="card-body">
                <h2 class="h5 mb-3">Lista</h2>

                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Título</th>
                                <th>Autor</th>
                                <th>Categoria</th>
                                <th>Criado em</th>
                                <th class="text-end">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($posts) === 0) { ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted">Nenhum post encontrado.</td>
                                </tr>
                            <?php } ?>

                            <?php foreach ($posts as $p) {
                                $criadoEm = (string)($p['criado_em'] ?? '');
                                $data = $criadoEm !== '' ? date('d/m/Y', strtotime($criadoEm)) : '—';
                            ?>
                                <tr>
                                    <td><?php echo e($p['id']); ?></td>
                                    <td><?php echo e($p['titulo']); ?></td>
                                    <td><?php echo e($p['autor_id'] ?? ''); ?></td>
                                    <td><?php echo e($p['categoria_id'] ?? ''); ?></td>
                                    <td><?php echo e($data); ?></td>
                                    <td class="text-end">
                                        <a class="btn btn-sm btn-outline-secondary" href="<?php echo e(baseUrl('/posts') . '?edit=' . (int)$p['id']); ?>">Editar</a>

                                        <form method="post" action="<?php echo e(baseUrl('/posts')); ?>" class="d-inline" onsubmit="return confirm('Remover este post?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo e($p['id']); ?>">
                                            <button class="btn btn-sm btn-outline-danger" type="submit">Excluir</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
