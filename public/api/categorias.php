<?php

require "../../src/Database.php";
require "../../src/models/Categoria.php";

$db = new Database();
$con = $db->conectar();
$cat = new Categoria($con);

$metodo = $_SERVER["REQUEST_METHOD"];

if ($metodo === "GET") {

    if (isset($_GET["id"])) {
        echo json_encode($cat->buscarPorId($_GET["id"]));
        exit;
    }

    echo json_encode(
        $cat->listar()->fetchAll(PDO::FETCH_ASSOC),
        JSON_UNESCAPED_UNICODE
    );
}

if ($metodo === "POST") {
    $cat->criar($_POST);
    echo "Categoria criada.";
}

if ($metodo === "PUT") {
    parse_str(file_get_contents("php://input"), $put);
    $cat->atualizar($_GET["id"], $put);
    echo "Categoria atualizada.";
}

if ($metodo === "DELETE") {
    $cat->deletar($_GET["id"]);
    echo "Categoria removida.";
}
