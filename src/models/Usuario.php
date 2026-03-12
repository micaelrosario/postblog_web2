<?php

class Usuario {

    private $conexao;

    public function __construct($conexao) {
        $this->conexao = $conexao;
    }

    public function listar() {
        return $this->conexao->query("SELECT * FROM usuarios ORDER BY id DESC");
    }

    public function criar($dados) {
        $sql = "INSERT INTO usuarios (username, first_name, last_name, email, senha)
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
        $stmt = $this->conexao->prepare("SELECT * FROM usuarios WHERE id=:id");
        $stmt->execute(['id'=>$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function atualizar($id, $dados) {
        $sql = "UPDATE usuarios SET username=:username, first_name=:first_name, 
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
