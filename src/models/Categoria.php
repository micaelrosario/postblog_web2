<?php

class Categoria
{
    private PDO $conexao;

    public function __construct(PDO $conexao)
    {
        $this->conexao = $conexao;
    }

    public function listar()
    {
        return $this->conexao->query('SELECT * FROM categoria ORDER BY id ASC');
    }

    public function criar(array $dados): bool
    {
        $stmt = $this->conexao->prepare('INSERT INTO categoria (nome) VALUES (:nome)');
        return $stmt->execute([
            'nome' => $dados['nome'] ?? '',
        ]);
    }

    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->conexao->prepare('SELECT * FROM categoria WHERE id = :id');
        $stmt->execute(['id' => $id]);

        $registro = $stmt->fetch();
        return $registro ?: null;
    }

    public function atualizar(int $id, array $dados): bool
    {
        $stmt = $this->conexao->prepare('UPDATE categoria SET nome = :nome WHERE id = :id');
        return $stmt->execute([
            'id' => $id,
            'nome' => $dados['nome'] ?? '',
        ]);
    }

    public function deletar(int $id): bool
    {
        $stmt = $this->conexao->prepare('DELETE FROM categoria WHERE id = :id');
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
