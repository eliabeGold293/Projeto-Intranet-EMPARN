<?php
require_once __DIR__ . '/../config/connection.php';

$data = json_decode(file_get_contents("php://input"), true);

$stmt = $pdo->prepare("
    UPDATE projeto_usuario
    SET papel_id = :papel_id
    WHERE projeto_id = :projeto_id
      AND usuario_id = :usuario_id
");

$stmt->execute([
    ":papel_id"   => $data["papel_id"],
    ":projeto_id" => $data["projeto_id"],
    ":usuario_id" => $data["usuario_id"]
]);

echo json_encode([
    "status" => "success",
    "linhas_afetadas" => $stmt->rowCount()
]);