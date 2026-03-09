<?php

declare(strict_types=1);

final class PostRepository
{
    private PDO $conn;
    private string $tableName = 'post';

    public function __construct(PDO $db)
    {
        $this->conn = $db;
    }

    /** @return array<int, array<string, mixed>> */
    public function all(): array
    {
        $sql = "SELECT * FROM `{$this->tableName}` ORDER BY criado_em DESC";
        return $this->conn->query($sql)->fetchAll();
    }

    /** @return array<string, mixed>|null */
    public function find(int $id): ?array
    {
        $stmt = $this->conn->prepare("SELECT * FROM `{$this->tableName}` WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): int
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO `{$this->tableName}` (titulo, conteudo, imagem, perfil_autor, criado_em) VALUES (:titulo, :conteudo, :imagem, :perfil_autor, NOW())"
        );

        $stmt->execute([
            ':titulo' => (string)($data['titulo'] ?? ''),
            ':conteudo' => (string)($data['conteudo'] ?? ''),
            ':imagem' => (string)($data['imagem'] ?? ''),
            ':perfil_autor' => (string)($data['perfil_autor'] ?? ''),
        ]);

        return (int)$this->conn->lastInsertId();
    }

    /** @param array<string, mixed> $data */
    public function update(int $id, array $data): bool
    {
        $stmt = $this->conn->prepare(
            "UPDATE `{$this->tableName}` SET titulo = :titulo, conteudo = :conteudo, imagem = :imagem, perfil_autor = :perfil_autor WHERE id = :id"
        );

        return $stmt->execute([
            ':id' => $id,
            ':titulo' => (string)($data['titulo'] ?? ''),
            ':conteudo' => (string)($data['conteudo'] ?? ''),
            ':imagem' => (string)($data['imagem'] ?? ''),
            ':perfil_autor' => (string)($data['perfil_autor'] ?? ''),
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->conn->prepare("DELETE FROM `{$this->tableName}` WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
