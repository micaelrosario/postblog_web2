<?php

class Comentario {

    private $conexao;

    public function __construct($conexao) {
        $this->conexao = $conexao;
    }

    public function listar() {
        return $this->conexao->query("SELECT * FROM comentario ORDER BY criado_em DESC");
    }

    public function criar($dados) {
        $sql = "INSERT INTO comentario (post_id, autor_id, texto) 
                VALUES (:post_id, :autor_id, :texto)";
        $stmt = $this->conexao->prepare($sql);
        return $stmt->execute([
            ':post_id' => $dados['post_id'],
            ':autor_id' => $dados['autor_id'],
            ':texto' => $dados['texto']
        ]);
    }

    public function buscarPorId($id) {
        $stmt = $this->conexao->prepare("SELECT * FROM comentario WHERE id=:id");
        $stmt->execute(['id'=>$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function atualizar($id, $dados) {
        $sql = "UPDATE comentario SET texto=:texto WHERE id=:id";
        $stmt = $this->conexao->prepare($sql);
        return $stmt->execute([
            ':texto' => $dados['texto'],
            ':id' => $id
        ]);
    }

    public function deletar($id) {
        $stmt = $this->conexao->prepare("DELETE FROM comentario WHERE id=:id");
        return $stmt->execute(['id'=>$id]);
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
