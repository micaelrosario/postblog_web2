<?php

class Post
{
    private PDO $conexao;

    public function __construct(PDO $conexao)
    {
        $this->conexao = $conexao;
    }

    public function listar()
    {
        return $this->conexao->query('SELECT * FROM post ORDER BY criado_em DESC');
    }

    public function criar(array $dados): bool
    {
        $sql = "INSERT INTO post (titulo, autor_id, categoria_id, conteudo, imagem)
                VALUES (:titulo, :autor_id, :categoria_id, :conteudo, :imagem)";

        $stmt = $this->conexao->prepare($sql);

        return $stmt->execute([
            'titulo' => $dados['titulo'] ?? '',
            'autor_id' => $dados['autor_id'] ?? $dados['usuario_id'] ?? 1,
            'categoria_id' => $dados['categoria_id'] ?? 1,
            'conteudo' => $dados['conteudo'] ?? '',
            'imagem' => $dados['imagem'] ?? null,
        ]);
    }

    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->conexao->prepare('SELECT * FROM post WHERE id = :id');
        $stmt->execute(['id' => $id]);

        $registro = $stmt->fetch();
        return $registro ?: null;
    }

    public function atualizar(int $id, array $dados): bool
    {
        $sql = "UPDATE post
                SET titulo=:titulo,
                    autor_id=:autor_id,
                    categoria_id=:categoria_id,
                    conteudo=:conteudo,
                    imagem=:imagem
                WHERE id = :id";

        $stmt = $this->conexao->prepare($sql);

        return $stmt->execute([
            'titulo' => $dados['titulo'] ?? '',
            'autor_id' => $dados['usuario_id'] ?? $dados['autor_id'] ?? 1,
            'categoria_id' => $dados['categoria_id'] ?? 1,
            'conteudo' => $dados['conteudo'] ?? '',
            'imagem' => $dados['imagem'] ?? null,
            'id' => $id,
        ]);
    }

    public function deletar(int $id): bool
    {
        $stmt = $this->conexao->prepare('DELETE FROM post WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    // Padrão simples (para o roteador central): GET/POST/PUT/DELETE
    public function get($id = null)
    {
        if ($id === null) {
            return $this->listar()->fetchAll();
        }

        return $this->buscarPorId((int)$id);
    }

    public function post($dados)
    {
        return $this->criar((array)$dados);
    }

    public function put($id, $dados)
    {
        return $this->atualizar((int)$id, (array)$dados);
    }

    public function delete($id)
    {
        return $this->deletar((int)$id);
    }
}
