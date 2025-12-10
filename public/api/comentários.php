<?php

require "../../src/Database.php";
require "../../src/models/Comentario.php";

$db = new Database();
$con = $db->conectar();
$coment = new Comentario($con);

$metodo = $_SERVER["REQUEST_METHOD"];

if ($metodo === "GET") {

    if (isset($_GET["id"])) {
        echo json_encode($coment->buscarPorId($_GET["id"]));
        exit;
    }

    echo json_encode(
        $coment->listar()->fetchAll(PDO::FETCH_ASSOC),
        JSON_UNESCAPED_UNICODE
    );
}

if ($metodo === "POST") {
    $coment->criar($_POST);
    echo "Comentário criado.";
}

if ($metodo === "PUT") {
    parse_str(file_get_contents("php://input"), $put);
    $coment->atualizar($_GET["id"], $put);
    echo "Comentário atualizado.";
}

if ($metodo === "DELETE") {
    $coment->deletar($_GET["id"]);
    echo "Comentário apagado.";
}
