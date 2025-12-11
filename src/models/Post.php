<?php

class Post {

    private $con;

    public function __construct($con) {
        $this->con = $con;
    }

    public function listar() {
        return $this->con->query("SELECT * FROM post ORDER BY criado_em DESC");
    }

    public function criar($dados) {
        $sql = "INSERT INTO post (titulo, autor_id, categoria_id, conteudo, imagem)
                VALUES (:titulo, :autor_id, :categoria_id, :conteudo, :imagem)";

        $stmt = $this->con->prepare($sql);

        return $stmt->execute([
            ':titulo'       => $dados['titulo'] ?? '',
            ':autor_id'     => $dados['autor_id'] ?? $dados['usuario_id'] ?? 1,
            ':categoria_id' => $dados['categoria_id'] ?? 1,
            ':conteudo'     => $dados['conteudo'] ?? '',
            ':imagem'       => $dados['imagem'] ?? null
        ]);
    }


    public function buscarPorId($id) {
        $sql = "SELECT * FROM post WHERE id = :id";
        $stmt = $this->con->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function atualizar($id, $dados) {
        $sql = "UPDATE post 
                SET titulo=:titulo, 
                    autor_id=:autor_id, 
                    categoria_id=:categoria_id,
                    conteudo=:conteudo, 
                    imagem=:imagem
                WHERE id = :id";

        $stmt = $this->con->prepare($sql);

        
        return $stmt->execute([
            ':titulo'       => $dados['titulo'] ?? '',
            ':autor_id'     => $dados['usuario_id'] ?? $dados['autor_id'] ?? 1,
            ':categoria_id' => $dados['categoria_id'] ?? 1,
            ':conteudo'     => $dados['conteudo'] ?? '',
            ':imagem'       => $dados['imagem'] ?? null,
            ':id'           => $id
        ]);
    }


    public function deletar($id) {
        $stmt = $this->con->prepare("DELETE FROM post WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
