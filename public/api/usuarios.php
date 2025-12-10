<?php

require "../../src/Database.php";
require "../../src/models/Usuario.php";

$db = new Database();
$con = $db->conectar();
$user = new Usuario($con);

$metodo = $_SERVER["REQUEST_METHOD"];

if ($metodo === "GET") {

    if (isset($_GET["id"])) {
        echo json_encode($user->buscarPorId($_GET["id"]));
        exit;
    }

    echo json_encode(
        $user->listar()->fetchAll(PDO::FETCH_ASSOC),
        JSON_UNESCAPED_UNICODE
    );
}

if ($metodo === "POST") {
    // Hash seguro
    $_POST['senha'] = password_hash($_POST['senha'], PASSWORD_DEFAULT);
    $user->criar($_POST);
    echo "Usuário criado.";
}

if ($metodo === "PUT") {
    parse_str(file_get_contents("php://input"), $put);
    $user->atualizar($_GET["id"], $put);
    echo "Usuário atualizado.";
}

if ($metodo === "DELETE") {
    $user->deletar($_GET["id"]);
    echo "Usuário apagado.";
}
