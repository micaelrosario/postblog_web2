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
        $sql = "INSERT INTO usuarios (username, senha, data_criacao)
                VALUES (:username, :senha, NOW())";
        $stmt = $this->con->prepare($sql);
        return $stmt->execute($dados);
    }

    public function buscarPorId($id) {
        $stmt = $this->con->prepare("SELECT * FROM usuarios WHERE id=:id");
        $stmt->execute(['id'=>$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function atualizar($id, $dados) {
        $dados['id'] = $id;
        $stmt = $this->con->prepare("UPDATE usuarios SET username=:username WHERE id=:id");
        return $stmt->execute($dados);
    }

    public function deletar($id) {
        $stmt = $this->con->prepare("DELETE FROM usuarios WHERE id=:id");
        return $stmt->execute(['id'=>$id]);
    }
}
