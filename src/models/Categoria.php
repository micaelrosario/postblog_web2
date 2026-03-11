<?php

class Categoria {

    private $con;

    public function __construct($con) {
        $this->con = $con;
    }

    public function listar() {
        return $this->con->query("SELECT * FROM categoria ORDER BY id ASC");
    }

    public function criar($dados) {
        $stmt = $this->con->prepare("INSERT INTO categoria (nome) VALUES (:nome)");
        return $stmt->execute($dados);
    }

    public function buscarPorId($id) {
        $stmt = $this->con->prepare("SELECT * FROM categoria WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function atualizar($id, $dados) {
        $stmt = $this->con->prepare("UPDATE categoria SET nome = :nome WHERE id = :id");
        $dados['id'] = $id;
        return $stmt->execute($dados);
    }

    public function deletar($id) {
        $stmt = $this->con->prepare("DELETE FROM categoria WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
