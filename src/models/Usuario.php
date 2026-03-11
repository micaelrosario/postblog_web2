<?php

defined('ACCESS') or die('Acesso negado');

class Usuario {

    private $con;

    public function __construct($con) {
        $this->con = $con;
    }

    public function listar() {
        return $this->con->query("SELECT * FROM usuarios ORDER BY id DESC");
    }

    public function criar($dados) {
        $sql = "INSERT INTO usuarios (username, first_name, last_name, email, senha)
                VALUES (:username, :first_name, :last_name, :email, :senha)";
        $stmt = $this->con->prepare($sql);
        return $stmt->execute([
            ':username' => $dados['username'],
            ':first_name' => $dados['first_name'] ?? null,
            ':last_name' => $dados['last_name'] ?? null,
            ':email' => $dados['email'] ?? null,
            ':senha' => $dados['senha']
        ]);
    }

    public function buscarPorId($id) {
        $stmt = $this->con->prepare("SELECT * FROM usuarios WHERE id=:id");
        $stmt->execute(['id'=>$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function atualizar($id, $dados) {
        $sql = "UPDATE usuarios SET username=:username, first_name=:first_name, 
                last_name=:last_name, email=:email WHERE id=:id";
        $stmt = $this->con->prepare($sql);
        return $stmt->execute([
            ':username' => $dados['username'],
            ':first_name' => $dados['first_name'] ?? null,
            ':last_name' => $dados['last_name'] ?? null,
            ':email' => $dados['email'] ?? null,
            ':id' => $id
        ]);
    }

    public function deletar($id) {
        $stmt = $this->con->prepare("DELETE FROM usuarios WHERE id=:id");
        return $stmt->execute(['id'=>$id]);
    }

    // Padrão simples (para o roteador central): GET/POST/PUT/DELETE
    public function get($id = null) {
        if ($id === null) {
            return $this->listar()->fetchAll(PDO::FETCH_ASSOC);
        }

        $row = $this->buscarPorId($id);
        return $row ?: null;
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
