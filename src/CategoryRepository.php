<?php

declare(strict_types=1);

final class CategoryRepository
{
    private PDO $conn;
    private string $tableName = 'categoria';

    public function __construct(PDO $db)
    {
        $this->conn = $db;
    }

    /** @return array<int, array<string, mixed>> */
    public function all(): array
    {
        $sql = "SELECT id, nome FROM `{$this->tableName}` ORDER BY nome ASC";
        return $this->conn->query($sql)->fetchAll();
    }

    /** @return array<string, mixed>|null */
    public function find(int $id): ?array
    {
        $stmt = $this->conn->prepare("SELECT id, nome FROM `{$this->tableName}` WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public function create(string $nome): int
    {
        $stmt = $this->conn->prepare("INSERT INTO `{$this->tableName}` (nome) VALUES (:nome)");
        $stmt->execute([':nome' => $nome]);
        return (int)$this->conn->lastInsertId();
    }

    public function update(int $id, string $nome): bool
    {
        $stmt = $this->conn->prepare("UPDATE `{$this->tableName}` SET nome = :nome WHERE id = :id");
        return $stmt->execute([':id' => $id, ':nome' => $nome]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->conn->prepare("DELETE FROM `{$this->tableName}` WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
