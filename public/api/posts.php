<?php

require "../../src/Database.php";
require "../../src/models/Post.php";

$db = new Database();
$con = $db->conectar();
$post = new Post($con);

$metodo = $_SERVER["REQUEST_METHOD"];

if ($metodo === "GET") {

    if (isset($_GET["id"])) {
        echo json_encode($post->buscarPorId($_GET["id"]));
        exit;
    }

    echo json_encode(
        $post->listar()->fetchAll(PDO::FETCH_ASSOC),
        JSON_UNESCAPED_UNICODE
    );
}

if ($metodo === "POST") {
    $post->criar($_POST);
    echo "Post criado.";
}

if ($metodo === "PUT") {
    parse_str(file_get_contents("php://input"), $put);
    $post->atualizar($_GET["id"], $put);
    echo "Post atualizado.";
}

if ($metodo === "DELETE") {
    $post->deletar($_GET["id"]);
    echo "Post removido.";
}
