<?php

declare(strict_types=1);

defined('ACCESS') or die('Acesso negado');

class Database
{
    private string $host = 'localhost';
    private string $dbname = 'blogpost';
    private string $username = 'root';
    private string $password = '';

    private ?PDO $conn = null;

    public function conectar(): PDO
    {
        if ($this->conn instanceof PDO) {
            return $this->conn;
        }

        try {
            $this->conn = new PDO(
                "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );

            return $this->conn;
        } catch (PDOException $e) {
            throw new RuntimeException('Erro ao conectar no banco: ' . $e->getMessage(), 0, $e);
        }
    }
}
