<?php
session_start();
require_once __DIR__ . '/../config/connection.php';

header('Content-Type: application/json');

try {

    if(!isset($_SESSION['usuario_id'])){
        echo json_encode([
            "status"=>"error",
            "message"=>"Sessão inválida"
        ]);
        exit;
    }

    $senha = $_POST['senha'] ?? '';

    if(empty($senha)){
        echo json_encode([
            "status"=>"error",
            "message"=>"Senha não informada"
        ]);
        exit;
    }

    $sql = "SELECT senha FROM usuario WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':id'=>$_SESSION['usuario_id']
    ]);

    $usuario = $stmt->fetch();

    if(!$usuario){
        echo json_encode([
            "status"=>"error",
            "message"=>"Usuário não encontrado"
        ]);
        exit;
    }

    if(!password_verify($senha,$usuario['senha'])){
        echo json_encode([
            "status"=>"error",
            "message"=>"Senha incorreta"
        ]);
        exit;
    }

    echo json_encode([
        "status"=>"success",
        "grau_acesso"=>$_SESSION['grau_acesso']
    ]);

} catch(Exception $e){

    echo json_encode([
        "status"=>"error",
        "message"=>"Erro interno"
    ]);
}