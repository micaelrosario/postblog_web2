<?php

class PerfilAutor
{
    private PDO $conexao;

    public function __construct(PDO $conexao)
    {
        $this->conexao = $conexao;
    }

    public function listar()
    {
        return $this->conexao->query('SELECT * FROM perfil_autor');
    }

    public function criar(array $dados): bool
    {
        $sql = 'INSERT INTO perfil_autor (usuario_id, bio, foto, redes_sociais)
                VALUES (:usuario_id, :bio, :foto, :redes_sociais)';
        $stmt = $this->conexao->prepare($sql);
        return $stmt->execute([
            'usuario_id' => $dados['usuario_id'] ?? null,
            'bio' => $dados['bio'] ?? null,
            'foto' => $dados['foto'] ?? null,
            'redes_sociais' => $dados['redes_sociais'] ?? null,
        ]);
    }

    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->conexao->prepare('SELECT * FROM perfil_autor WHERE id = :id');
        $stmt->execute(['id' => $id]);

        $registro = $stmt->fetch();
        return $registro ?: null;
    }

    public function atualizar(int $id, array $dados): bool
    {
        $stmt = $this->conexao->prepare(
            'UPDATE perfil_autor
            SET bio = :bio, foto = :foto, redes_sociais = :redes_sociais
            WHERE id = :id'
        );
        return $stmt->execute([
            'id' => $id,
            'bio' => $dados['bio'] ?? null,
            'foto' => $dados['foto'] ?? null,
            'redes_sociais' => $dados['redes_sociais'] ?? null,
        ]);
    }

    public function deletar(int $id): bool
    {
        $stmt = $this->conexao->prepare('DELETE FROM perfil_autor WHERE id = :id');
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
