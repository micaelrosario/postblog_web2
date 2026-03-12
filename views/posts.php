<?php

$idEdicao = (int)($_GET['edit'] ?? 0);
$postEdicao = $idEdicao > 0 ? $modeloPost->get($idEdicao) : null;

$usuarios = $modeloUsuario->get();
$categorias = $modeloCategoria->get();
$posts = $modeloPost->get();

$usuarioPorId = [];
foreach ($usuarios as $usuario) {
    $id = (int)($usuario['id'] ?? 0);
    if ($id <= 0) {
        continue;
    }

    $nomeExibicao = (string)($usuario['username'] ?? '');
    if ($nomeExibicao === '') {
        $nome = trim((string)($usuario['first_name'] ?? '') . ' ' . (string)($usuario['last_name'] ?? ''));
        $nomeExibicao = $nome !== '' ? $nome : ('Usuário #' . $id);
    }

    $usuarioPorId[$id] = $nomeExibicao;
}

$categoriaPorId = [];
foreach ($categorias as $categoria) {
    $id = (int)($categoria['id'] ?? 0);
    if ($id <= 0) {
        continue;
    }

    $categoriaPorId[$id] = (string)($categoria['nome'] ?? '');
}

$acaoFormulario = $postEdicao
    ? baseUrl('/posts/' . (int)($postEdicao['id'] ?? 0))
    : baseUrl('/posts');

?>

<h1 class="h3 mb-3">Posts</h1>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card">
            <div class="card-body">
                <h2 class="h5 mb-3"><?php echo $postEdicao ? 'Editar Post' : 'Novo Post'; ?></h2>

                <form method="post"
                    action="<?php echo e($acaoFormulario); ?>"
                    <?php if ($postEdicao) { ?>data-metodo-rest="PUT" data-redirecionar="<?php echo e(baseUrl('/posts')); ?>"<?php } ?>
                >

                    <div class="mb-3">
                        <label class="form-label" for="titulo">Título</label>
                        <input class="form-control" id="titulo" name="titulo" required value="<?php echo e($postEdicao['titulo'] ?? ''); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="autor_id">Autor</label>
                        <select class="form-select" id="autor_id" name="autor_id" required>
                            <?php foreach ($usuarios as $usuario) {
                                $selecionado = ($postEdicao && (int)($postEdicao['autor_id'] ?? 0) === (int)$usuario['id']) ? 'selected' : '';
                                $nomeExibicao = $usuario['username'] ?? ($usuario['first_name'] ?? ('Usuário #' . $usuario['id']));
                            ?>
                                <option value="<?php echo e($usuario['id']); ?>" <?php echo $selecionado; ?>><?php echo e($nomeExibicao); ?></option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="categoria_id">Categoria</label>
                        <select class="form-select" id="categoria_id" name="categoria_id" required>
                            <?php foreach ($categorias as $categoria) {
                                $selecionado = ($postEdicao && (int)($postEdicao['categoria_id'] ?? 0) === (int)$categoria['id']) ? 'selected' : '';
                            ?>
                                <option value="<?php echo e($categoria['id']); ?>" <?php echo $selecionado; ?>><?php echo e($categoria['nome']); ?></option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="conteudo">Conteúdo</label>
                        <textarea class="form-control" id="conteudo" name="conteudo" rows="5" required><?php echo e($postEdicao['conteudo'] ?? ''); ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="imagem">Imagem (URL)</label>
                        <input class="form-control" id="imagem" name="imagem" value="<?php echo e($postEdicao['imagem'] ?? ''); ?>">
                    </div>

                    <div class="d-flex gap-2">
                        <button class="btn btn-primary" type="submit">Salvar</button>
                        <?php if ($postEdicao) { ?>
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

                            <?php foreach ($posts as $post) {
                                $criadoEm = (string)($post['criado_em'] ?? '');
                                $data = $criadoEm !== '' ? date('d/m/Y', strtotime($criadoEm)) : '—';

                                $autorId = (int)($post['autor_id'] ?? 0);
                                $autorNome = $autorId > 0
                                    ? ($usuarioPorId[$autorId] ?? ('Usuário #' . $autorId))
                                    : '—';

                                $categoriaId = (int)($post['categoria_id'] ?? 0);
                                $categoriaNome = $categoriaId > 0
                                    ? (($categoriaPorId[$categoriaId] ?? '') !== '' ? $categoriaPorId[$categoriaId] : ('Categoria #' . $categoriaId))
                                    : '—';
                            ?>
                                <tr>
                                    <td><?php echo e($post['id']); ?></td>
                                    <td><?php echo e($post['titulo']); ?></td>
                                    <td><?php echo e($autorNome); ?></td>
                                    <td><?php echo e($categoriaNome); ?></td>
                                    <td><?php echo e($data); ?></td>
                                    <td class="text-end">
                                        <div class="d-flex flex-column align-items-end gap-2">
                                            <a class="btn btn-sm btn-outline-secondary" href="<?php echo e(baseUrl('/posts') . '?edit=' . (int)$post['id']); ?>">Editar</a>

                                            <form method="post" action="<?php echo e(baseUrl('/posts/' . (int)$post['id'])); ?>" class="m-0" data-metodo-rest="DELETE" data-redirecionar="<?php echo e(baseUrl('/posts')); ?>" onsubmit="return confirm('Remover este post?');">
                                                <button class="btn btn-sm btn-outline-danger" type="submit">Excluir</button>
                                            </form>
                                        </div>
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
