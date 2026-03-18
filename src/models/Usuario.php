<?php

class Usuario {

    private $conexao;
    private $colunaLogin;

    public function __construct($conexao) {
        $this->conexao = $conexao;
        $this->colunaLogin = $this->detectarColunaLogin();
    }

    private function detectarColunaLogin(): string {
        try {
            $stmt = $this->conexao->query("SHOW COLUMNS FROM usuarios LIKE 'username'");
            if ($stmt && $stmt->fetch(PDO::FETCH_ASSOC)) {
                return 'username';
            }

            $stmt = $this->conexao->query("SHOW COLUMNS FROM usuarios LIKE 'usuario'");
            if ($stmt && $stmt->fetch(PDO::FETCH_ASSOC)) {
                return 'usuario';
            }
        } catch (Throwable $e) {
            // Ignora e mantém padrão
        }

        return 'username';
    }

    private function colunaLoginSql(): string {
        return $this->colunaLogin === 'usuario' ? 'usuario' : 'username';
    }

    public function listar() {
        $col = $this->colunaLoginSql();
        return $this->conexao->query("SELECT id, {$col} AS username, first_name, last_name, email, criado_em FROM usuarios ORDER BY id DESC");
    }

    public function criar($dados) {
        $col = $this->colunaLoginSql();
        $sql = "INSERT INTO usuarios ({$col}, first_name, last_name, email, senha)
                VALUES (:username, :first_name, :last_name, :email, :senha)";
        $stmt = $this->conexao->prepare($sql);
        return $stmt->execute([
            ':username' => $dados['username'],
            ':first_name' => $dados['first_name'] ?? null,
            ':last_name' => $dados['last_name'] ?? null,
            ':email' => $dados['email'] ?? null,
            ':senha' => $dados['senha']
        ]);
    }

    public function buscarPorId($id) {
        $col = $this->colunaLoginSql();
        $stmt = $this->conexao->prepare("SELECT id, {$col} AS username, first_name, last_name, email, criado_em FROM usuarios WHERE id=:id");
        $stmt->execute(['id'=>$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function buscarPorUsername(string $username): ?array {
        $col = $this->colunaLoginSql();
        $stmt = $this->conexao->prepare("SELECT id, {$col} AS username, first_name, last_name, email, senha, criado_em FROM usuarios WHERE {$col} = :username LIMIT 1");
        $stmt->execute(['username' => $username]);
        $registro = $stmt->fetch(PDO::FETCH_ASSOC);

        return $registro ?: null;
    }

    public function atualizar($id, $dados) {
        $col = $this->colunaLoginSql();
        $sql = "UPDATE usuarios SET {$col}=:username, first_name=:first_name, 
                last_name=:last_name, email=:email WHERE id=:id";
        $stmt = $this->conexao->prepare($sql);
        return $stmt->execute([
            ':username' => $dados['username'],
            ':first_name' => $dados['first_name'] ?? null,
            ':last_name' => $dados['last_name'] ?? null,
            ':email' => $dados['email'] ?? null,
            ':id' => $id
        ]);
    }

    public function deletar($id) {
        $stmt = $this->conexao->prepare("DELETE FROM usuarios WHERE id=:id");
        return $stmt->execute(['id'=>$id]);
    }

    // Padrão simples (para o roteador central): GET/POST/PUT/DELETE
    public function get($id = null) {
        if ($id === null) {
            return $this->listar()->fetchAll(PDO::FETCH_ASSOC);
        }

        $registro = $this->buscarPorId($id);
        return $registro ?: null;
    }

    public function post($dados) {
        if (!isset($dados['senha'])) {
            return false;
        }

        $dados['senha'] = password_hash((string)$dados['senha'], PASSWORD_DEFAULT);
        return $this->criar($dados);
    }

    public function put($id, $dados) {
        return $this->atualizar($id, $dados);
    }

    public function delete($id) {
        return $this->deletar($id);
    }
}
