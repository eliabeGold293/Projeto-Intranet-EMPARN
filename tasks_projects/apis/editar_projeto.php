<?php
require_once __DIR__ . '/../config/connection.php';

$data = json_decode(file_get_contents("php://input"), true);

$id = $data['id'] ?? null;
$campo = $data['campo'] ?? null;
$valor = $data['valor'] ?? null;

$permitidos = ["titulo","status","data_inicio","data_fim"];

if (!$id || !in_array($campo, $permitidos)) {
    echo json_encode([
        "status"=>"error",
        "message"=>"Campo inválido"
    ]);
    exit;
}

try {

    $sql = "UPDATE projeto
            SET $campo = :valor,
                data_modificacao = NOW()
            WHERE id = :id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":valor"=>$valor,
        ":id"=>$id
    ]);

    echo json_encode([
        "status"=>"success",
        "message"=>"Atualizado com sucesso"
    ]);

} catch (Exception $e){

    echo json_encode([
        "status"=>"error",
        "message"=>$e->getMessage()
    ]);
}
