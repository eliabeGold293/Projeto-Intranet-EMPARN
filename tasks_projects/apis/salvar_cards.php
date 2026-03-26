<?php

require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/../utils/log-action.php';
session_start();

header("Content-Type: application/json");

try {
    $titulo = $_POST['titulo'] ?? null;
    $cor    = $_POST['cor'] ?? null;
    $link   = $_POST['link'] ?? null;

    if (!$titulo || !$cor || !$link) {
        throw new Exception("Dados incompletos.");
    }

    // ================================
    // INSERT
    // ================================
    $sql = "INSERT INTO dashboard (titulo, cor, link) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$titulo, $cor, $link]);

    $id = $pdo->lastInsertId();

    // ================================
    // LOG (PADRÃO NOVO)
    // ================================
    try {
        registrarLog(
            $pdo,
            $_SESSION['usuario_id'] ?? null,
            'dashboard',
            'CREATE',
            "Card '{$titulo}' (ID {$id}) criado"
        );
    } catch (Exception $e) {
        error_log("Erro ao registrar log: " . $e->getMessage());
    }

    // ================================
    // RESPOSTA
    // ================================
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