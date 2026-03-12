<?php

class Database
{
    private $servidor = 'localhost';
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
    }
}
