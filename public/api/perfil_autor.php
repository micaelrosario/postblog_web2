<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

require "../../src/database.php";
require "../../src/models/PerfilAutor.php";

$db = new Database();
$con = $db->conectar();
$perfil = new PerfilAutor($con);

$metodo = $_SERVER["REQUEST_METHOD"];

if ($metodo === "GET") {
    if (isset($_GET["id"])) {
        $resultado = $perfil->buscarPorId($_GET["id"]);
        echo json_encode($resultado ? $resultado : ["erro" => "Perfil não encontrado"]);
        exit;
    }
    echo json_encode($perfil->listar()->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE);
}

if ($metodo === "POST") {
    if ($perfil->criar($_POST)) {
        echo json_encode(["sucesso" => true, "mensagem" => "Perfil criado com sucesso"]);
    } else {
        echo json_encode(["sucesso" => false, "mensagem" => "Erro ao criar perfil"]);
    }
}

if ($metodo === "PUT") {
    parse_str(file_get_contents("php://input"), $put);
    if (isset($_GET["id"]) && $perfil->atualizar($_GET["id"], $put)) {
        echo json_encode(["sucesso" => true, "mensagem" => "Perfil atualizado com sucesso"]);
    } else {
        echo json_encode(["sucesso" => false, "mensagem" => "Erro ao atualizar perfil"]);
    }
}

if ($metodo === "DELETE") {
    if (isset($_GET["id"]) && $perfil->deletar($_GET["id"])) {
        echo json_encode(["sucesso" => true, "mensagem" => "Perfil removido com sucesso"]);
    } else {
        echo json_encode(["sucesso" => false, "mensagem" => "Erro ao remover perfil"]);
    }
}
