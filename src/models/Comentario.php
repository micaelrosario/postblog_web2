<?php

class Comentario
{
    private PDO $conexao;

    public function __construct(PDO $conexao)
    {
        $this->conexao = $conexao;
    }

    public function listar()
    {
        return $this->conexao->query('SELECT * FROM comentario ORDER BY criado_em DESC');
    }

    public function listarPorPostId(int $postId): array
    {
        $stmt = $this->conexao->prepare('SELECT * FROM comentario WHERE post_id = :post_id ORDER BY criado_em DESC');
        $stmt->execute(['post_id' => $postId]);
        return $stmt->fetchAll();
    }

    public function criar(array $dados): bool
    {
        $sql = 'INSERT INTO comentario (post_id, autor_id, texto)
                VALUES (:post_id, :autor_id, :texto)';
        $stmt = $this->conexao->prepare($sql);
        return $stmt->execute([
            'post_id' => (int)($dados['post_id'] ?? 0),
            'autor_id' => (int)($dados['autor_id'] ?? 0),
            'texto' => (string)($dados['texto'] ?? ''),
        ]);
    }

    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->conexao->prepare('SELECT * FROM comentario WHERE id = :id');
        $stmt->execute(['id' => $id]);

        $registro = $stmt->fetch();
        return $registro ?: null;
    }

    public function atualizar(int $id, array $dados): bool
    {
        $sql = 'UPDATE comentario SET texto = :texto WHERE id = :id';
        $stmt = $this->conexao->prepare($sql);
        return $stmt->execute([
            'texto' => (string)($dados['texto'] ?? ''),
            'id' => $id,
        ]);
    }

    public function deletar(int $id): bool
    {
        $stmt = $this->conexao->prepare('DELETE FROM comentario WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    // Padrão simples (para o roteador central): GET/POST/PUT/DELETE
    public function get($id = null)
    {
        if ($id === null) {
            return $this->listar()->fetchAll();
        }

        return $this->buscarPorId((int)$id);
    }

    public function post($dados)
    {
        return $this->criar((array)$dados);
    }

    public function put($id, $dados)
    {
        return $this->atualizar((int)$id, (array)$dados);
    }

    public function delete($id)
    {
        return $this->deletar((int)$id);
    }
}
