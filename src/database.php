<?php
class Database {

    private $host = "localhost";
    private $db_name = "blogpost";
    private $username = "root";
    private $password = "";
    public $conn;

    public function conectar(){
        try {
            $this->conn = new PDO(
                "mysql:host={$this->host};dbname={$this->db_name}",
                $this->username,
                $this->password
            );
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $this->conn;

        } catch (PDOException $e){
            die("Erro: " . $e->getMessage());
        }
    }
}
