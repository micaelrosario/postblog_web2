<?php

$idEdicao = (int)($_GET['edit'] ?? 0);
$comentarioEdicao = $idEdicao > 0 ? $modeloComentario->get($idEdicao) : null;

$comentarios = $modeloComentario->get();
$posts = $modeloPost->get();
$usuarios = $modeloUsuario->get();

$acaoFormulario = $comentarioEdicao
    ? baseUrl('/comentarios/' . (int)($comentarioEdicao['id'] ?? 0))
    : baseUrl('/comentarios');

?>

<h1 class="h3 mb-3">Comentários</h1>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card">
            <div class="card-body">
                <h2 class="h5 mb-3"><?php echo $comentarioEdicao ? 'Editar Comentário' : 'Novo Comentário'; ?></h2>

                <form method="post"
                    action="<?php echo e($acaoFormulario); ?>"
                    <?php if ($comentarioEdicao) { ?>data-metodo-rest="PUT" data-redirecionar="<?php echo e(baseUrl('/comentarios')); ?>"<?php } ?>
                >

                    <div class="mb-3">
                        <label class="form-label" for="post_id">Post</label>
                        <select class="form-select" id="post_id" name="post_id" <?php echo $comentarioEdicao ? 'disabled' : 'required'; ?>>
                            <?php foreach ($posts as $post) {
                                $selecionado = ($comentarioEdicao && (int)($comentarioEdicao['post_id'] ?? 0) === (int)$post['id']) ? 'selected' : '';
                            ?>
                                <option value="<?php echo e($post['id']); ?>" <?php echo $selecionado; ?>><?php echo e('#' . $post['id'] . ' - ' . $post['titulo']); ?></option>
                            <?php } ?>
                        </select>
                        <?php if ($comentarioEdicao) { ?>
                            <input type="hidden" name="post_id" value="<?php echo e($comentarioEdicao['post_id']); ?>">
                        <?php } ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="autor_id">Autor</label>
                        <select class="form-select" id="autor_id" name="autor_id" <?php echo $comentarioEdicao ? 'disabled' : 'required'; ?>>
                            <?php foreach ($usuarios as $usuario) {
                                $selecionado = ($comentarioEdicao && (int)($comentarioEdicao['autor_id'] ?? 0) === (int)$usuario['id']) ? 'selected' : '';
                                $nomeExibicao = $usuario['username'] ?? ($usuario['first_name'] ?? ('Usuário #' . $usuario['id']));
                            ?>
                                <option value="<?php echo e($usuario['id']); ?>" <?php echo $selecionado; ?>><?php echo e($nomeExibicao); ?></option>
                            <?php } ?>
                        </select>
                        <?php if ($comentarioEdicao) { ?>
                            <input type="hidden" name="autor_id" value="<?php echo e($comentarioEdicao['autor_id']); ?>">
                        <?php } ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="texto">Texto</label>
                        <textarea class="form-control" id="texto" name="texto" rows="4" required><?php echo e($comentarioEdicao['texto'] ?? ''); ?></textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button class="btn btn-primary" type="submit">Salvar</button>
                        <?php if ($comentarioEdicao) { ?>
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

                            <?php foreach ($comentarios as $comentario) {
                                $texto = (string)($comentario['texto'] ?? '');
                                $resumo = strlen($texto) > 60 ? (substr($texto, 0, 60) . '...') : $texto;
                                $criadoEm = (string)($comentario['criado_em'] ?? '');
                                $data = $criadoEm !== '' ? date('d/m/Y', strtotime($criadoEm)) : '—';
                            ?>
                                <tr>
                                    <td><?php echo e($comentario['id']); ?></td>
                                    <td><?php echo e($comentario['post_id']); ?></td>
                                    <td><?php echo e($comentario['autor_id']); ?></td>
                                    <td><?php echo e($resumo); ?></td>
                                    <td><?php echo e($data); ?></td>
                                    <td class="text-end">
                                        <a class="btn btn-sm btn-outline-secondary" href="<?php echo e(baseUrl('/comentarios') . '?edit=' . (int)$comentario['id']); ?>">Editar</a>

                                        <form method="post" action="<?php echo e(baseUrl('/comentarios/' . (int)$comentario['id'])); ?>" class="d-inline" data-metodo-rest="DELETE" data-redirecionar="<?php echo e(baseUrl('/comentarios')); ?>" onsubmit="return confirm('Remover este comentário?');">
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
