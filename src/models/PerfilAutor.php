<?php

class PerfilAutor {

    private $con;

    public function __construct($con) {
        $this->con = $con;
    }

    public function listar() {
        return $this->con->query("SELECT * FROM perfil_autor");
    }

    public function criar($dados) {
        $sql = "INSERT INTO perfil_autor (usuario_id, bio, foto, redes_sociais)
                VALUES (:usuario_id, :bio, :foto, :redes_sociais)";
        $stmt = $this->con->prepare($sql);
        return $stmt->execute($dados);
    }

    public function buscarPorId($id) {
        $stmt = $this->con->prepare("SELECT * FROM perfil_autor WHERE id=:id");
        $stmt->execute(['id'=>$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function atualizar($id, $dados) {
        $dados['id'] = $id;
        $stmt = $this->con->prepare("
            UPDATE perfil_autor 
            SET bio=:bio, foto=:foto, redes_sociais=:redes_sociais
            WHERE id=:id
        ");
        return $stmt->execute($dados);
    }

    public function deletar($id) {
        $stmt = $this->con->prepare("DELETE FROM perfil_autor WHERE id=:id");
        return $stmt->execute(['id'=>$id]);
    }
}
