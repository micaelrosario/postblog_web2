<?php

declare(strict_types=1);

defined('ACCESS') or die('Acesso negado');

class Database
{
    private string $servidor = 'localhost';
    private string $banco = 'blogpost';
    private string $usuario = 'root';
    private string $senha = '';

    private ?PDO $conexao = null;

    public function conectar(): PDO
    {
        if ($this->conexao instanceof PDO) {
            return $this->conexao;
        }

        try {
            $this->conexao = new PDO(
                "mysql:host={$this->servidor};dbname={$this->banco};charset=utf8mb4",
                $this->usuario,
                $this->senha,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );

            return $this->conexao;
        } catch (PDOException $e) {
            throw new RuntimeException('Erro ao conectar no banco: ' . $e->getMessage(), 0, $e);
        }
    }
}
