<?php

defined('ACCESS') or die('Acesso negado');

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
        return $stmt->execute([
            'usuario_id' => $dados['usuario_id'] ?? null,
            'bio' => $dados['bio'] ?? null,
            'foto' => $dados['foto'] ?? null,
            'redes_sociais' => $dados['redes_sociais'] ?? null,
        ]);
    }

    public function buscarPorId($id) {
        $stmt = $this->con->prepare("SELECT * FROM perfil_autor WHERE id=:id");
        $stmt->execute(['id'=>$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function atualizar($id, $dados) {
        $stmt = $this->con->prepare("
            UPDATE perfil_autor 
            SET bio=:bio, foto=:foto, redes_sociais=:redes_sociais
            WHERE id=:id
        ");
        return $stmt->execute([
            'id' => $id,
            'bio' => $dados['bio'] ?? null,
            'foto' => $dados['foto'] ?? null,
            'redes_sociais' => $dados['redes_sociais'] ?? null,
        ]);
    }

    public function deletar($id) {
        $stmt = $this->con->prepare("DELETE FROM perfil_autor WHERE id=:id");
        return $stmt->execute(['id'=>$id]);
    }

    // Padrão simples (para o roteador central): GET/POST/PUT/DELETE
    public function get($id = null) {
        if ($id === null) {
            return $this->listar()->fetchAll(PDO::FETCH_ASSOC);
        }

        $row = $this->buscarPorId($id);
        return $row ?: null;
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
