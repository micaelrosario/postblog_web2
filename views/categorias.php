<?php

defined('ACCESS') or die('Acesso negado');

$editId = (int)($_GET['edit'] ?? 0);
$editCategoria = $editId > 0 ? $categoriaModel->get($editId) : null;

$categorias = $categoriaModel->get();

?>

<h1 class="h3 mb-3">Categorias</h1>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card">
            <div class="card-body">
                <h2 class="h5 mb-3"><?php echo $editCategoria ? 'Editar Categoria' : 'Nova Categoria'; ?></h2>

                <form method="post" action="<?php echo e(baseUrl('/categorias')); ?>">
                    <input type="hidden" name="action" value="<?php echo $editCategoria ? 'update' : 'create'; ?>">
                    <?php if ($editCategoria) { ?>
                        <input type="hidden" name="id" value="<?php echo e($editCategoria['id']); ?>">
                    <?php } ?>

                    <div class="mb-3">
                        <label class="form-label" for="nome">Nome</label>
                        <input class="form-control" id="nome" name="nome" required value="<?php echo e($editCategoria['nome'] ?? ''); ?>">
                    </div>

                    <div class="d-flex gap-2">
                        <button class="btn btn-primary" type="submit">Salvar</button>
                        <?php if ($editCategoria) { ?>
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

                            <?php foreach ($categorias as $c) { ?>
                                <tr>
                                    <td><?php echo e($c['id']); ?></td>
                                    <td><?php echo e($c['nome']); ?></td>
                                    <td class="text-end">
                                        <a class="btn btn-sm btn-outline-secondary" href="<?php echo e(baseUrl('/categorias') . '?edit=' . (int)$c['id']); ?>">Editar</a>

                                        <form method="post" action="<?php echo e(baseUrl('/categorias')); ?>" class="d-inline" onsubmit="return confirm('Remover esta categoria?');">
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
