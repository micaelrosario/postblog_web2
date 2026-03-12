<?php

class Post {

    private $conexao;

    public function __construct($conexao) {
        $this->conexao = $conexao;
    }

    public function listar() {
        return $this->conexao->query("SELECT * FROM post ORDER BY criado_em DESC");
    }

    public function criar($dados) {
        $sql = "INSERT INTO post (titulo, autor_id, categoria_id, conteudo, imagem)
                VALUES (:titulo, :autor_id, :categoria_id, :conteudo, :imagem)";

        $stmt = $this->conexao->prepare($sql);

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
        $stmt = $this->conexao->prepare($sql);
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

        $stmt = $this->conexao->prepare($sql);

        
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
        $stmt = $this->conexao->prepare("DELETE FROM post WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    // Padrão simples (para o roteador central): GET/POST/PUT/DELETE
    public function get($id = null) {
        if ($id === null) {
            return $this->listar()->fetchAll(PDO::FETCH_ASSOC);
        }

        $registro = $this->buscarPorId($id);
        return $registro ?: null;
    }

    public function post($dados) {
        return $this->criar($dados);
    }

    public function put($id, $dados) {
        return $this->atualizar($id, $dados);
    }

    public function delete($id) {
        return $this->deletar($id);
    }
}
