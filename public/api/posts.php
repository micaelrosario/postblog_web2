<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

require "../../src/database.php";
require "../../src/models/Post.php";

$db = new Database();
$con = $db->conectar();
$post = new Post($con);

$metodo = $_SERVER["REQUEST_METHOD"];

if ($metodo === "GET") {
    if (isset($_GET["id"])) {
        $resultado = $post->buscarPorId($_GET["id"]);
        echo json_encode($resultado ? $resultado : ["erro" => "Post não encontrado"]);
        exit;
    }
    echo json_encode($post->listar()->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE);
}

if ($metodo === "POST") {
    $dados = $_POST;
    if ($post->criar($dados)) {
        echo json_encode(["sucesso" => true, "mensagem" => "Post criado com sucesso"]);
    } else {
        echo json_encode(["sucesso" => false, "mensagem" => "Erro ao criar post"]);
    }
}

if ($metodo === "PUT") {
    parse_str(file_get_contents("php://input"), $put);
    if (isset($_GET["id"]) && $post->atualizar($_GET["id"], $put)) {
        echo json_encode(["sucesso" => true, "mensagem" => "Post atualizado com sucesso"]);
    } else {
        echo json_encode(["sucesso" => false, "mensagem" => "Erro ao atualizar post"]);
    }
}

if ($metodo === "DELETE") {
    if (isset($_GET["id"]) && $post->deletar($_GET["id"])) {
        echo json_encode(["sucesso" => true, "mensagem" => "Post removido com sucesso"]);
    } else {
        echo json_encode(["sucesso" => false, "mensagem" => "Erro ao remover post"]);
    }
}
