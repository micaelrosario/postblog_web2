<?php

$idEdicao = (int)($_GET['edit'] ?? 0);
$perfilEdicao = $idEdicao > 0 ? $modeloPerfil->get($idEdicao) : null;

$perfis = $modeloPerfil->get();
$usuarios = $modeloUsuario->get();

$acaoFormulario = $perfilEdicao
    ? baseUrl('/perfis/' . (int)($perfilEdicao['id'] ?? 0))
    : baseUrl('/perfis');

?>

<h1 class="h3 mb-3">Perfis</h1>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card">
            <div class="card-body">
                <h2 class="h5 mb-3"><?php echo $perfilEdicao ? 'Editar Perfil' : 'Novo Perfil'; ?></h2>

                <form method="post"
                    action="<?php echo e($acaoFormulario); ?>"
                    <?php if ($perfilEdicao) { ?>data-metodo-rest="PUT" data-redirecionar="<?php echo e(baseUrl('/perfis')); ?>"<?php } ?>
                >

                    <div class="mb-3">
                        <label class="form-label" for="usuario_id">Usuário</label>
                        <select class="form-select" id="usuario_id" name="usuario_id" <?php echo $perfilEdicao ? 'disabled' : 'required'; ?>>
                            <?php foreach ($usuarios as $usuario) {
                                $selecionado = ($perfilEdicao && (int)($perfilEdicao['usuario_id'] ?? 0) === (int)$usuario['id']) ? 'selected' : '';
                                $nomeExibicao = $usuario['username'] ?? ($usuario['first_name'] ?? ('Usuário #' . $usuario['id']));
                            ?>
                                <option value="<?php echo e($usuario['id']); ?>" <?php echo $selecionado; ?>><?php echo e($nomeExibicao); ?></option>
                            <?php } ?>
                        </select>
                        <?php if ($perfilEdicao) { ?>
                            <input type="hidden" name="usuario_id" value="<?php echo e($perfilEdicao['usuario_id']); ?>">
                            <div class="form-text">O usuário do perfil não é alterado na edição.</div>
                        <?php } ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="bio">Bio</label>
                        <textarea class="form-control" id="bio" name="bio" rows="4"><?php echo e($perfilEdicao['bio'] ?? ''); ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="foto">Foto (URL)</label>
                        <input class="form-control" id="foto" name="foto" value="<?php echo e($perfilEdicao['foto'] ?? ''); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="redes_sociais">Redes sociais (texto/JSON)</label>
                        <input class="form-control" id="redes_sociais" name="redes_sociais" value="<?php echo e($perfilEdicao['redes_sociais'] ?? ''); ?>">
                    </div>

                    <div class="d-flex gap-2">
                        <button class="btn btn-primary" type="submit">Salvar</button>
                        <?php if ($perfilEdicao) { ?>
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

                            <?php foreach ($perfis as $perfil) {
                                $bio = (string)($perfil['bio'] ?? '');
                                $resumo = strlen($bio) > 60 ? (substr($bio, 0, 60) . '...') : $bio;
                            ?>
                                <tr>
                                    <td><?php echo e($perfil['id']); ?></td>
                                    <td><?php echo e($perfil['usuario_id']); ?></td>
                                    <td><?php echo e($resumo); ?></td>
                                    <td class="text-end">
                                        <a class="btn btn-sm btn-outline-secondary" href="<?php echo e(baseUrl('/perfis') . '?edit=' . (int)$perfil['id']); ?>">Editar</a>

                                        <form method="post" action="<?php echo e(baseUrl('/perfis/' . (int)$perfil['id'])); ?>" class="d-inline" data-metodo-rest="DELETE" data-redirecionar="<?php echo e(baseUrl('/perfis')); ?>" onsubmit="return confirm('Remover este perfil?');">
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
