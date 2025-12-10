<?php

class Comentario {

    private $con;

    public function __construct($con) {
        $this->con = $con;
    }

    public function listar() {
        return $this->con->query("SELECT * FROM comentarios ORDER BY criado_em DESC");
    }

    public function criar($dados) {
        $sql = "INSERT INTO comentarios (post_id, autor_id, texto) 
                VALUES (:post_id, :autor_id, :texto)";
        $stmt = $this->con->prepare($sql);
        return $stmt->execute($dados);
    }

    public function buscarPorId($id) {
        $stmt = $this->con->prepare("SELECT * FROM comentarios WHERE id=:id");
        $stmt->execute(['id'=>$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function atualizar($id, $dados) {
        $sql = "UPDATE comentarios SET texto=:texto WHERE id=:id";
        $stmt = $this->con->prepare($sql);
        $dados['id'] = $id;
        return $stmt->execute($dados);
    }

    public function deletar($id) {
        $stmt = $this->con->prepare("DELETE FROM comentarios WHERE id=:id");
        return $stmt->execute(['id'=>$id]);
    }
}
