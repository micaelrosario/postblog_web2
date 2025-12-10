<?php

class Categoria {

    private $con;

    public function __construct($con) {
        $this->con = $con;
    }

    public function listar() {
        return $this->con->query("SELECT * FROM categorias ORDER BY nome ASC");
    }

    public function criar($dados) {
        $stmt = $this->con->prepare("INSERT INTO categorias (nome) VALUES (:nome)");
        return $stmt->execute($dados);
    }

    public function buscarPorId($id) {
        $stmt = $this->con->prepare("SELECT * FROM categorias WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function atualizar($id, $dados) {
        $stmt = $this->con->prepare("UPDATE categorias SET nome = :nome WHERE id = :id");
        $dados['id'] = $id;
        return $stmt->execute($dados);
    }

    public function deletar($id) {
        $stmt = $this->con->prepare("DELETE FROM categorias WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
