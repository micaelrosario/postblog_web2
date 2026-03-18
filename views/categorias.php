<?php

final class CategoriasView
{
    public static function render(array $dados): void
    {
        $categoriaEdicao = $dados['categoriaEdicao'] ?? null;
        $categorias = $dados['categorias'] ?? [];
        $acaoFormulario = (string)($dados['acaoFormulario'] ?? Http::baseUrl('/categorias'));

        ?>

        <h1 class="h3 mb-3">Categorias</h1>

        <div class="row g-4">
            <div class="col-lg-5">
                <div class="card">
                    <div class="card-body">
                        <h2 class="h5 mb-3"><?php echo $categoriaEdicao ? 'Editar Categoria' : 'Nova Categoria'; ?></h2>

                        <form method="post"
                            action="<?php echo Http::e($acaoFormulario); ?>"
                            <?php if ($categoriaEdicao) { ?>data-metodo-rest="PUT" data-redirecionar="<?php echo Http::e(Http::baseUrl('/categorias')); ?>"<?php } ?>
                        >

                            <div class="mb-3">
                                <label class="form-label" for="nome">Nome</label>
                                <input class="form-control" id="nome" name="nome" required value="<?php echo Http::e($categoriaEdicao['nome'] ?? ''); ?>">
                            </div>

                            <div class="d-flex gap-2">
                                <button class="btn btn-primary" type="submit">Salvar</button>
                                <?php if ($categoriaEdicao) { ?>
                                    <a class="btn btn-outline-secondary" href="<?php echo Http::e(Http::baseUrl('/categorias')); ?>">Cancelar</a>
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
                                            <td><?php echo Http::e($categoria['id'] ?? ''); ?></td>
                                            <td><?php echo Http::e($categoria['nome'] ?? ''); ?></td>
                                            <td class="text-end">
                                                <a class="btn btn-sm btn-outline-secondary" href="<?php echo Http::e(Http::baseUrl('/categorias') . '?edit=' . (int)($categoria['id'] ?? 0)); ?>">Editar</a>

                                                <form method="post" action="<?php echo Http::e(Http::baseUrl('/categorias/' . (int)($categoria['id'] ?? 0))); ?>" class="d-inline" data-metodo-rest="DELETE" data-redirecionar="<?php echo Http::e(Http::baseUrl('/categorias')); ?>" onsubmit="return confirm('Remover esta categoria?');">
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
        <?php
    }
}
