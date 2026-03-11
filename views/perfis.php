<?php

defined('ACCESS') or die('Acesso negado');

$editId = (int)($_GET['edit'] ?? 0);
$editPerfil = $editId > 0 ? $perfilModel->get($editId) : null;

$perfis = $perfilModel->get();
$usuarios = $usuarioModel->get();

?>

<h1 class="h3 mb-3">Perfis</h1>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card">
            <div class="card-body">
                <h2 class="h5 mb-3"><?php echo $editPerfil ? 'Editar Perfil' : 'Novo Perfil'; ?></h2>

                <form method="post" action="<?php echo e(baseUrl('/perfis')); ?>">
                    <input type="hidden" name="action" value="<?php echo $editPerfil ? 'update' : 'create'; ?>">
                    <?php if ($editPerfil) { ?>
                        <input type="hidden" name="id" value="<?php echo e($editPerfil['id']); ?>">
                    <?php } ?>

                    <div class="mb-3">
                        <label class="form-label" for="usuario_id">Usuário</label>
                        <select class="form-select" id="usuario_id" name="usuario_id" <?php echo $editPerfil ? 'disabled' : 'required'; ?>>
                            <?php foreach ($usuarios as $u) {
                                $selected = ($editPerfil && (int)($editPerfil['usuario_id'] ?? 0) === (int)$u['id']) ? 'selected' : '';
                                $label = $u['username'] ?? ($u['first_name'] ?? ('Usuário #' . $u['id']));
                            ?>
                                <option value="<?php echo e($u['id']); ?>" <?php echo $selected; ?>><?php echo e($label); ?></option>
                            <?php } ?>
                        </select>
                        <?php if ($editPerfil) { ?>
                            <input type="hidden" name="usuario_id" value="<?php echo e($editPerfil['usuario_id']); ?>">
                            <div class="form-text">O usuário do perfil não é alterado na edição.</div>
                        <?php } ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="bio">Bio</label>
                        <textarea class="form-control" id="bio" name="bio" rows="4"><?php echo e($editPerfil['bio'] ?? ''); ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="foto">Foto (URL)</label>
                        <input class="form-control" id="foto" name="foto" value="<?php echo e($editPerfil['foto'] ?? ''); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="redes_sociais">Redes sociais (texto/JSON)</label>
                        <input class="form-control" id="redes_sociais" name="redes_sociais" value="<?php echo e($editPerfil['redes_sociais'] ?? ''); ?>">
                    </div>

                    <div class="d-flex gap-2">
                        <button class="btn btn-primary" type="submit">Salvar</button>
                        <?php if ($editPerfil) { ?>
                            <a class="btn btn-outline-secondary" href="<?php echo e(baseUrl('/perfis')); ?>">Cancelar</a>
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
                                <th>Usuário</th>
                                <th>Bio</th>
                                <th class="text-end">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($perfis) === 0) { ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Nenhum perfil encontrado.</td>
                                </tr>
                            <?php } ?>

                            <?php foreach ($perfis as $p) {
                                $bio = (string)($p['bio'] ?? '');
                                $resumo = strlen($bio) > 60 ? (substr($bio, 0, 60) . '...') : $bio;
                            ?>
                                <tr>
                                    <td><?php echo e($p['id']); ?></td>
                                    <td><?php echo e($p['usuario_id']); ?></td>
                                    <td><?php echo e($resumo); ?></td>
                                    <td class="text-end">
                                        <a class="btn btn-sm btn-outline-secondary" href="<?php echo e(baseUrl('/perfis') . '?edit=' . (int)$p['id']); ?>">Editar</a>

                                        <form method="post" action="<?php echo e(baseUrl('/perfis')); ?>" class="d-inline" onsubmit="return confirm('Remover este perfil?');">
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
