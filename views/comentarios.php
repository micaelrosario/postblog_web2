<?php

defined('ACCESS') or die('Acesso negado');

$editId = (int)($_GET['edit'] ?? 0);
$editComentario = $editId > 0 ? $comentarioModel->get($editId) : null;

$comentarios = $comentarioModel->get();
$posts = $postModel->get();
$usuarios = $usuarioModel->get();

?>

<h1 class="h3 mb-3">Comentários</h1>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card">
            <div class="card-body">
                <h2 class="h5 mb-3"><?php echo $editComentario ? 'Editar Comentário' : 'Novo Comentário'; ?></h2>

                <form method="post" action="<?php echo e(baseUrl('/comentarios')); ?>">
                    <input type="hidden" name="action" value="<?php echo $editComentario ? 'update' : 'create'; ?>">
                    <?php if ($editComentario) { ?>
                        <input type="hidden" name="id" value="<?php echo e($editComentario['id']); ?>">
                    <?php } ?>

                    <div class="mb-3">
                        <label class="form-label" for="post_id">Post</label>
                        <select class="form-select" id="post_id" name="post_id" <?php echo $editComentario ? 'disabled' : 'required'; ?>>
                            <?php foreach ($posts as $p) {
                                $selected = ($editComentario && (int)($editComentario['post_id'] ?? 0) === (int)$p['id']) ? 'selected' : '';
                            ?>
                                <option value="<?php echo e($p['id']); ?>" <?php echo $selected; ?>><?php echo e('#' . $p['id'] . ' - ' . $p['titulo']); ?></option>
                            <?php } ?>
                        </select>
                        <?php if ($editComentario) { ?>
                            <input type="hidden" name="post_id" value="<?php echo e($editComentario['post_id']); ?>">
                        <?php } ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="autor_id">Autor</label>
                        <select class="form-select" id="autor_id" name="autor_id" <?php echo $editComentario ? 'disabled' : 'required'; ?>>
                            <?php foreach ($usuarios as $u) {
                                $selected = ($editComentario && (int)($editComentario['autor_id'] ?? 0) === (int)$u['id']) ? 'selected' : '';
                                $label = $u['username'] ?? ($u['first_name'] ?? ('Usuário #' . $u['id']));
                            ?>
                                <option value="<?php echo e($u['id']); ?>" <?php echo $selected; ?>><?php echo e($label); ?></option>
                            <?php } ?>
                        </select>
                        <?php if ($editComentario) { ?>
                            <input type="hidden" name="autor_id" value="<?php echo e($editComentario['autor_id']); ?>">
                        <?php } ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="texto">Texto</label>
                        <textarea class="form-control" id="texto" name="texto" rows="4" required><?php echo e($editComentario['texto'] ?? ''); ?></textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button class="btn btn-primary" type="submit">Salvar</button>
                        <?php if ($editComentario) { ?>
                            <a class="btn btn-outline-secondary" href="<?php echo e(baseUrl('/comentarios')); ?>">Cancelar</a>
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
                                <th>Post</th>
                                <th>Autor</th>
                                <th>Texto</th>
                                <th>Criado em</th>
                                <th class="text-end">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($comentarios) === 0) { ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted">Nenhum comentário encontrado.</td>
                                </tr>
                            <?php } ?>

                            <?php foreach ($comentarios as $c) {
                                $texto = (string)($c['texto'] ?? '');
                                $resumo = strlen($texto) > 60 ? (substr($texto, 0, 60) . '...') : $texto;
                                $criadoEm = (string)($c['criado_em'] ?? '');
                                $data = $criadoEm !== '' ? date('d/m/Y', strtotime($criadoEm)) : '—';
                            ?>
                                <tr>
                                    <td><?php echo e($c['id']); ?></td>
                                    <td><?php echo e($c['post_id']); ?></td>
                                    <td><?php echo e($c['autor_id']); ?></td>
                                    <td><?php echo e($resumo); ?></td>
                                    <td><?php echo e($data); ?></td>
                                    <td class="text-end">
                                        <a class="btn btn-sm btn-outline-secondary" href="<?php echo e(baseUrl('/comentarios') . '?edit=' . (int)$c['id']); ?>">Editar</a>

                                        <form method="post" action="<?php echo e(baseUrl('/comentarios')); ?>" class="d-inline" onsubmit="return confirm('Remover este comentário?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo e($c['id']); ?>">
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
