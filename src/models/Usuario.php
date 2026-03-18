<?php

class Usuario
{
    private PDO $conexao;
    private string $colunaLogin;

    public function __construct(PDO $conexao)
    {
        $this->conexao = $conexao;
        $this->colunaLogin = $this->detectarColunaLogin();
    }

    private function detectarColunaLogin(): string
    {
        try {
            foreach (['username', 'usuario'] as $coluna) {
                $stmt = $this->conexao->query("SHOW COLUMNS FROM usuarios LIKE '{$coluna}'");
                if ($stmt && $stmt->fetch()) {
                    return $coluna;
                }
            }
        } catch (Throwable $e) {
            // Ignora e mantém padrão
        }

        return 'username';
    }

    private function colunaLoginSql(): string
    {
        return $this->colunaLogin === 'usuario' ? 'usuario' : 'username';
    }

    public function listar()
    {
        $col = $this->colunaLoginSql();
        return $this->conexao->query("SELECT id, {$col} AS username, first_name, last_name, email, criado_em FROM usuarios ORDER BY id DESC");
    }

    public function criar(array $dados): bool
    {
        $col = $this->colunaLoginSql();
        $sql = "INSERT INTO usuarios ({$col}, first_name, last_name, email, senha)
                VALUES (:username, :first_name, :last_name, :email, :senha)";

        $stmt = $this->conexao->prepare($sql);
        return $stmt->execute([
            'username' => $dados['username'],
            'first_name' => $dados['first_name'] ?? null,
            'last_name' => $dados['last_name'] ?? null,
            'email' => $dados['email'] ?? null,
            'senha' => $dados['senha'],
        ]);
    }

    public function buscarPorId(int $id): ?array
    {
        $col = $this->colunaLoginSql();
        $stmt = $this->conexao->prepare("SELECT id, {$col} AS username, first_name, last_name, email, criado_em FROM usuarios WHERE id=:id");
        $stmt->execute(['id' => $id]);

        $registro = $stmt->fetch();
        return $registro ?: null;
    }

    public function buscarPorUsername(string $username): ?array
    {
        $col = $this->colunaLoginSql();
        $stmt = $this->conexao->prepare("SELECT id, {$col} AS username, first_name, last_name, email, senha, criado_em FROM usuarios WHERE {$col} = :username LIMIT 1");
        $stmt->execute(['username' => $username]);

        $registro = $stmt->fetch();
        return $registro ?: null;
    }

    public function atualizar(int $id, array $dados): bool
    {
        $col = $this->colunaLoginSql();
        $sql = "UPDATE usuarios SET {$col}=:username, first_name=:first_name,
                last_name=:last_name, email=:email WHERE id=:id";

        $stmt = $this->conexao->prepare($sql);
        return $stmt->execute([
            'username' => $dados['username'],
            'first_name' => $dados['first_name'] ?? null,
            'last_name' => $dados['last_name'] ?? null,
            'email' => $dados['email'] ?? null,
            'id' => $id,
        ]);
    }

    public function deletar(int $id): bool
    {
        $stmt = $this->conexao->prepare('DELETE FROM usuarios WHERE id=:id');
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
        $dados = (array)$dados;
        if (!isset($dados['senha'])) {
            return false;
        }

        $dados['senha'] = password_hash((string)$dados['senha'], PASSWORD_DEFAULT);
        return $this->criar($dados);
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
