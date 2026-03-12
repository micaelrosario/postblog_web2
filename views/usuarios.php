<?php

defined('ACCESS') or die('Acesso negado');

$idEdicao = (int)($_GET['edit'] ?? 0);
$usuarioEdicao = $idEdicao > 0 ? $modeloUsuario->get($idEdicao) : null;

$usuarios = $modeloUsuario->get();

?>

<h1 class="h3 mb-3">Usuários</h1>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card">
            <div class="card-body">
                <h2 class="h5 mb-3"><?php echo $usuarioEdicao ? 'Editar Usuário' : 'Novo Usuário'; ?></h2>

                <form method="post" action="<?php echo e(baseUrl('/usuarios')); ?>">
                    <input type="hidden" name="action" value="<?php echo $usuarioEdicao ? 'update' : 'create'; ?>">
                    <?php if ($usuarioEdicao) { ?>
                        <input type="hidden" name="id" value="<?php echo e($usuarioEdicao['id']); ?>">
                    <?php } ?>

                    <div class="mb-3">
                        <label class="form-label" for="username">Username</label>
                        <input class="form-control" id="username" name="username" required value="<?php echo e($usuarioEdicao['username'] ?? ''); ?>">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="first_name">Nome</label>
                            <input class="form-control" id="first_name" name="first_name" value="<?php echo e($usuarioEdicao['first_name'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="last_name">Sobrenome</label>
                            <input class="form-control" id="last_name" name="last_name" value="<?php echo e($usuarioEdicao['last_name'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="email">Email</label>
                        <input class="form-control" id="email" name="email" type="email" value="<?php echo e($usuarioEdicao['email'] ?? ''); ?>">
                    </div>

                    <?php if (!$usuarioEdicao) { ?>
                        <div class="mb-3">
                            <label class="form-label" for="senha">Senha</label>
                            <input class="form-control" id="senha" name="senha" type="password" minlength="6" required>
                            <div class="form-text">Mínimo 6 caracteres.</div>
                        </div>
                    <?php } else { ?>
                        <div class="alert alert-secondary mb-3">
                            A senha não é alterada na edição (modelo atual só atualiza dados básicos).
                        </div>
                    <?php } ?>

                    <div class="d-flex gap-2">
                        <button class="btn btn-primary" type="submit">Salvar</button>
                        <?php if ($usuarioEdicao) { ?>
                            <a class="btn btn-outline-secondary" href="<?php echo e(baseUrl('/usuarios')); ?>">Cancelar</a>
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
                                <th>Username</th>
                                <th>Nome</th>
                                <th>Email</th>
                                <th>Criado em</th>
                                <th class="text-end">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($usuarios) === 0) { ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted">Nenhum usuário encontrado.</td>
                                </tr>
                            <?php } ?>

                            <?php foreach ($usuarios as $usuario) {
                                $nome = trim((string)($usuario['first_name'] ?? '') . ' ' . (string)($usuario['last_name'] ?? ''));
                                $criadoEm = (string)($usuario['criado_em'] ?? '');
                                $data = $criadoEm !== '' ? date('d/m/Y', strtotime($criadoEm)) : '—';
                            ?>
                                <tr>
                                    <td><?php echo e($usuario['id']); ?></td>
                                    <td><?php echo e($usuario['username'] ?? ''); ?></td>
                                    <td><?php echo e($nome); ?></td>
                                    <td><?php echo e($usuario['email'] ?? ''); ?></td>
                                    <td><?php echo e($data); ?></td>
                                    <td class="text-end">
                                        <a class="btn btn-sm btn-outline-secondary" href="<?php echo e(baseUrl('/usuarios') . '?edit=' . (int)$usuario['id']); ?>">Editar</a>

                                        <form method="post" action="<?php echo e(baseUrl('/usuarios')); ?>" class="d-inline" onsubmit="return confirm('Remover este usuário?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo e($usuario['id']); ?>">
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
