<?php

class Post {

    private $con;

    public function __construct($con) {
        $this->con = $con;
    }

    public function listar() {
        return $this->con->query("SELECT * FROM posts ORDER BY criado_em DESC");
    }

    public function criar($dados) {
        $sql = "INSERT INTO posts (titulo, autor_id, categoria_id, conteudo, imagem)
                VALUES (:titulo, :autor_id, :categoria_id, :conteudo, :imagem)";
        $stmt = $this->con->prepare($sql);
        return $stmt->execute($dados);
    }

    public function buscarPorId($id) {
        $sql = "SELECT * FROM posts WHERE id = :id";
        $stmt = $this->con->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function atualizar($id, $dados) {
        $sql = "UPDATE posts 
                SET titulo=:titulo, autor_id=:autor_id, categoria_id=:categoria_id,
                    conteudo=:conteudo, imagem=:imagem
                WHERE id = :id";
        $stmt = $this->con->prepare($sql);
        $dados['id'] = $id;
        return $stmt->execute($dados);
    }

    public function deletar($id) {
        $stmt = $this->con->prepare("DELETE FROM posts WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
