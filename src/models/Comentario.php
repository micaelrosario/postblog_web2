<?php

class Comentario {

    private $con;

    public function __construct($con) {
        $this->con = $con;
    }

    public function listar() {
        return $this->con->query("SELECT * FROM comentario ORDER BY criado_em DESC");
    }

    public function criar($dados) {
        $sql = "INSERT INTO comentario (post_id, autor_id, texto) 
                VALUES (:post_id, :autor_id, :texto)";
        $stmt = $this->con->prepare($sql);
        return $stmt->execute([
            ':post_id' => $dados['post_id'],
            ':autor_id' => $dados['autor_id'],
            ':texto' => $dados['texto']
        ]);
    }

    public function buscarPorId($id) {
        $stmt = $this->con->prepare("SELECT * FROM comentario WHERE id=:id");
        $stmt->execute(['id'=>$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function atualizar($id, $dados) {
        $sql = "UPDATE comentario SET texto=:texto WHERE id=:id";
        $stmt = $this->con->prepare($sql);
        return $stmt->execute([
            ':texto' => $dados['texto'],
            ':id' => $id
        ]);
    }

    public function deletar($id) {
        $stmt = $this->con->prepare("DELETE FROM comentario WHERE id=:id");
        return $stmt->execute(['id'=>$id]);
    }
}
