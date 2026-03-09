<?php
class Database {

    private $host = "localhost";
    private $dbname = "blogpost";
    private $username = "root";
    private $password = "";
    public $conn;

    public function conectar(){
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

        } catch (PDOException $e){
            throw new RuntimeException("Erro ao conectar no banco: " . $e->getMessage(), 0, $e);
        }
    }
}
