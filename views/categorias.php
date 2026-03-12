<?php

defined('ACCESS') or die('Acesso negado');

$idEdicao = (int)($_GET['edit'] ?? 0);
$categoriaEdicao = $idEdicao > 0 ? $modeloCategoria->get($idEdicao) : null;

$categorias = $modeloCategoria->get();

?>

<h1 class="h3 mb-3">Categorias</h1>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card">
            <div class="card-body">
                <h2 class="h5 mb-3"><?php echo $categoriaEdicao ? 'Editar Categoria' : 'Nova Categoria'; ?></h2>

                <form method="post" action="<?php echo e(baseUrl('/categorias')); ?>">
                    <input type="hidden" name="action" value="<?php echo $categoriaEdicao ? 'update' : 'create'; ?>">
                    <?php if ($categoriaEdicao) { ?>
                        <input type="hidden" name="id" value="<?php echo e($categoriaEdicao['id']); ?>">
                    <?php } ?>

                    <div class="mb-3">
                        <label class="form-label" for="nome">Nome</label>
                        <input class="form-control" id="nome" name="nome" required value="<?php echo e($categoriaEdicao['nome'] ?? ''); ?>">
                    </div>

                    <div class="d-flex gap-2">
                        <button class="btn btn-primary" type="submit">Salvar</button>
                        <?php if ($categoriaEdicao) { ?>
                            <a class="btn btn-outline-secondary" href="<?php echo e(baseUrl('/categorias')); ?>">Cancelar</a>
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
                                <th>Nome</th>
                                <th class="text-end">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($categorias) === 0) { ?>
                                <tr>
                                    <td colspan="3" class="text-center text-muted">Nenhuma categoria encontrada.</td>
                                </tr>
                            <?php } ?>

                            <?php foreach ($categorias as $categoria) { ?>
                                <tr>
                                    <td><?php echo e($categoria['id']); ?></td>
                                    <td><?php echo e($categoria['nome']); ?></td>
                                    <td class="text-end">
                                        <a class="btn btn-sm btn-outline-secondary" href="<?php echo e(baseUrl('/categorias') . '?edit=' . (int)$categoria['id']); ?>">Editar</a>

                                        <form method="post" action="<?php echo e(baseUrl('/categorias')); ?>" class="d-inline" onsubmit="return confirm('Remover esta categoria?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo e($categoria['id']); ?>">
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
