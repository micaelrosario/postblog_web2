<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

require "../../src/database.php";
require "../../src/models/Categoria.php";

$db = new Database();
$con = $db->conectar();
$cat = new Categoria($con);

$metodo = $_SERVER["REQUEST_METHOD"];

if ($metodo === "GET") {
    if (isset($_GET["id"])) {
        $resultado = $cat->buscarPorId($_GET["id"]);
        echo json_encode($resultado ? $resultado : ["erro" => "Categoria não encontrada"]);
        exit;
    }
    echo json_encode($cat->listar()->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE);
}

if ($metodo === "POST") {
    if ($cat->criar($_POST)) {
        echo json_encode(["sucesso" => true, "mensagem" => "Categoria criada com sucesso"]);
    } else {
        echo json_encode(["sucesso" => false, "mensagem" => "Erro ao criar categoria"]);
    }
}

if ($metodo === "PUT") {
    parse_str(file_get_contents("php://input"), $put);
    if (isset($_GET["id"]) && $cat->atualizar($_GET["id"], $put)) {
        echo json_encode(["sucesso" => true, "mensagem" => "Categoria atualizada com sucesso"]);
    } else {
        echo json_encode(["sucesso" => false, "mensagem" => "Erro ao atualizar categoria"]);
    }
}

if ($metodo === "DELETE") {
    if (isset($_GET["id"]) && $cat->deletar($_GET["id"])) {
        echo json_encode(["sucesso" => true, "mensagem" => "Categoria removida com sucesso"]);
    } else {
        echo json_encode(["sucesso" => false, "mensagem" => "Erro ao remover categoria"]);
    }
}
