<?php
header("Content-Type: application/json");
require_once __DIR__ . '/../config/connection.php';

$data = json_decode(file_get_contents("php://input"), true);

try {

    $id = (int) $data['id'];

    $stmt = $pdo->prepare("DELETE FROM tarefa WHERE id = :id");
    $stmt->execute([":id" => $id]);

    echo json_encode(["status" => "success"]);

} catch (Throwable $e) {

    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}