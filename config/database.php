<?php

class Database
{
    private $servidor = '127.0.0.1';
    private $porta = 3306;
    private $banco = 'blogpost';
    private $usuario = 'root';
    private $senha = '';

    private $conexao = null;

    public function conectar()
    {
        if ($this->conexao instanceof PDO) {
            return $this->conexao;
        }

        $this->conexao = new PDO(
            "mysql:host={$this->servidor};port={$this->porta};dbname={$this->banco};charset=utf8mb4",
            $this->usuario,
            $this->senha,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );

        return $this->conexao;
    }
}
