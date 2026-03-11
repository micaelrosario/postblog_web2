<?php

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
}
