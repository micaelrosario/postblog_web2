<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

require "../../src/database.php";
require "../../src/models/Comentario.php";

$db = new Database();
$con = $db->conectar();
$coment = new Comentario($con);

$metodo = $_SERVER["REQUEST_METHOD"];

if ($metodo === "GET") {
    if (isset($_GET["id"])) {
        $resultado = $coment->buscarPorId($_GET["id"]);
        echo json_encode($resultado ? $resultado : ["erro" => "Comentário não encontrado"]);
        exit;
    }
    echo json_encode($coment->listar()->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE);
}

if ($metodo === "POST") {
    try {
        if ($coment->criar($_POST)) {
            echo json_encode(["sucesso" => true, "mensagem" => "Comentário criado com sucesso"]);
        } else {
            echo json_encode(["sucesso" => false, "mensagem" => "Erro ao criar comentário"]);
        }
    } catch (Exception $e) {
        echo json_encode(["sucesso" => false, "mensagem" => "Erro: " . $e->getMessage()]);
    }
}

if ($metodo === "PUT") {
    parse_str(file_get_contents("php://input"), $put);
    if (isset($_GET["id"]) && $coment->atualizar($_GET["id"], $put)) {
        echo json_encode(["sucesso" => true, "mensagem" => "Comentário atualizado com sucesso"]);
    } else {
        echo json_encode(["sucesso" => false, "mensagem" => "Erro ao atualizar comentário"]);
    }
}

if ($metodo === "DELETE") {
    if (isset($_GET["id"]) && $coment->deletar($_GET["id"])) {
        echo json_encode(["sucesso" => true, "mensagem" => "Comentário removido com sucesso"]);
    } else {
        echo json_encode(["sucesso" => false, "mensagem" => "Erro ao remover comentário"]);
    }
}
