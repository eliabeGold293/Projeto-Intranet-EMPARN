<?php
require_once "../config/connection.php";

header("Content-Type: application/json");

try {
    $id = intval($_GET['id'] ?? 0);
    if ($id <= 0) {
        throw new Exception("ID inválido.");
    }

    $stmt = $pdo->prepare("DELETE FROM dashboard WHERE id = ?");
    $stmt->execute([$id]);

    echo json_encode([
        "status" => "success",
        "message" => "Card excluído com sucesso!"
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
