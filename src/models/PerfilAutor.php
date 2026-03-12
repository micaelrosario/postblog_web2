<?php

class PerfilAutor {

    private $conexao;

    public function __construct($conexao) {
        $this->conexao = $conexao;
    }

    public function listar() {
        return $this->conexao->query("SELECT * FROM perfil_autor");
    }

    public function criar($dados) {
        $sql = "INSERT INTO perfil_autor (usuario_id, bio, foto, redes_sociais)
                VALUES (:usuario_id, :bio, :foto, :redes_sociais)";
        $stmt = $this->conexao->prepare($sql);
        return $stmt->execute([
            'usuario_id' => $dados['usuario_id'] ?? null,
            'bio' => $dados['bio'] ?? null,
            'foto' => $dados['foto'] ?? null,
            'redes_sociais' => $dados['redes_sociais'] ?? null,
        ]);
    }

    public function buscarPorId($id) {
        $stmt = $this->conexao->prepare("SELECT * FROM perfil_autor WHERE id=:id");
        $stmt->execute(['id'=>$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function atualizar($id, $dados) {
        $stmt = $this->conexao->prepare("
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
        $stmt = $this->conexao->prepare("DELETE FROM perfil_autor WHERE id=:id");
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
