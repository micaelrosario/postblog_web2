<?php

class Categoria {

    private $conexao;

    public function __construct($conexao) {
        $this->conexao = $conexao;
    }

    public function listar() {
        return $this->conexao->query("SELECT * FROM categoria ORDER BY id ASC");
    }

    public function criar($dados) {
        $stmt = $this->conexao->prepare("INSERT INTO categoria (nome) VALUES (:nome)");
        return $stmt->execute([
            'nome' => $dados['nome'] ?? '',
        ]);
    }

    public function buscarPorId($id) {
        $stmt = $this->conexao->prepare("SELECT * FROM categoria WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function atualizar($id, $dados) {
        $stmt = $this->conexao->prepare("UPDATE categoria SET nome = :nome WHERE id = :id");
        return $stmt->execute([
            'id' => $id,
            'nome' => $dados['nome'] ?? '',
        ]);
    }

    public function deletar($id) {
        $stmt = $this->conexao->prepare("DELETE FROM categoria WHERE id = :id");
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
