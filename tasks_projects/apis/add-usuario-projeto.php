<?php
require_once __DIR__ . '/../config/connection.php';

$data = json_decode(file_get_contents("php://input"), true);

$stmt = $pdo->prepare("
    INSERT INTO projeto_usuario (projeto_id, usuario_id, papel_id)
    VALUES (?, ?, ?)
");

try{
    $stmt->execute([
        $data['projeto_id'],
        $data['usuario_id'],
        $data['papel_id']
    ]);

    echo json_encode(["status"=>"success"]);

}catch(PDOException $e){
    echo json_encode([
        "status"=>"error",
        "message"=>"Usuário já vinculado"
    ]);
}
