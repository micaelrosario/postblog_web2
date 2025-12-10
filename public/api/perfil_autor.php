<?php

require "../../src/Database.php";
require "../../src/models/PerfilAutor.php";

$db = new Database();
$con = $db->conectar();
$perfil = new PerfilAutor($con);

$metodo = $_SERVER["REQUEST_METHOD"];

if ($metodo === "GET") {

    if (isset($_GET["id"])) {
        echo json_encode($perfil->buscarPorId($_GET["id"]));
        exit;
    }

    echo json_encode(
        $perfil->listar()->fetchAll(PDO::FETCH_ASSOC),
        JSON_UNESCAPED_UNICODE
    );
}

if ($metodo === "POST") {
    $perfil->criar($_POST);
    echo "Perfil criado.";
}

if ($metodo === "PUT") {
    parse_str(file_get_contents("php://input"), $put);
    $perfil->atualizar($_GET["id"], $put);
    echo "Perfil atualizado.";
}

if ($metodo === "DELETE") {
    $perfil->deletar($_GET["id"]);
    echo "Perfil removido.";
}
