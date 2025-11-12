<?php

require_once "../config/connection.php";
header("Content-Type: application/json");

try {
    $titulo = $_POST['titulo'] ?? null;
    $cor    = $_POST['cor'] ?? null;
    $link   = $_POST['link'] ?? null;

    if (!$titulo || !$cor || !$link) {
        throw new Exception("Dados incompletos.");
    }

    $sql = "INSERT INTO dashboard (titulo, cor, link) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$titulo, $cor, $link]);

    // Pega o ID recém inserido
    $id = $pdo->lastInsertId();

    echo json_encode([
        "status" => "success",
        "id" => $id,
        "titulo" => $titulo,
        "cor" => $cor,
        "link" => $link
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}

?>