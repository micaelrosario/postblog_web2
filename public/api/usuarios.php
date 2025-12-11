<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

require "../../src/database.php";
require "../../src/models/Usuario.php";

$db = new Database();
$con = $db->conectar();
$user = new Usuario($con);

$metodo = $_SERVER["REQUEST_METHOD"];

if ($metodo === "GET") {
    if (isset($_GET["id"])) {
        $resultado = $user->buscarPorId($_GET["id"]);
        echo json_encode($resultado ? $resultado : ["erro" => "Usuário não encontrado"]);
        exit;
    }
    echo json_encode($user->listar()->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE);
}

if ($metodo === "POST") {
    try {
        $_POST['senha'] = password_hash($_POST['senha'], PASSWORD_DEFAULT);
        if ($user->criar($_POST)) {
            echo json_encode(["sucesso" => true, "mensagem" => "Usuário criado com sucesso"]);
        } else {
            echo json_encode(["sucesso" => false, "mensagem" => "Erro ao criar usuário"]);
        }
    } catch (Exception $e) {
        echo json_encode(["sucesso" => false, "mensagem" => "Erro: " . $e->getMessage()]);
    }
}

if ($metodo === "PUT") {
    parse_str(file_get_contents("php://input"), $put);
    if (isset($_GET["id"]) && $user->atualizar($_GET["id"], $put)) {
        echo json_encode(["sucesso" => true, "mensagem" => "Usuário atualizado com sucesso"]);
    } else {
        echo json_encode(["sucesso" => false, "mensagem" => "Erro ao atualizar usuário"]);
    }
}

if ($metodo === "DELETE") {
    if (isset($_GET["id"]) && $user->deletar($_GET["id"])) {
        echo json_encode(["sucesso" => true, "mensagem" => "Usuário removido com sucesso"]);
    } else {
        echo json_encode(["sucesso" => false, "mensagem" => "Erro ao remover usuário"]);
    }
}
