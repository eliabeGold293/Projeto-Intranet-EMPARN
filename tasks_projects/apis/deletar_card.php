<?php
require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/../utils/log-action.php';
session_start();

header("Content-Type: application/json");

try {
    $id = intval($_GET['id'] ?? 0);

    if ($id <= 0) {
        throw new Exception("ID inválido.");
    }

    // ================================
    // BUSCAR DADOS ANTES DE EXCLUIR
    // ================================
    $stmtTitulo = $pdo->prepare("SELECT titulo FROM dashboard WHERE id = ?");
    $stmtTitulo->execute([$id]);
    $card = $stmtTitulo->fetch(PDO::FETCH_ASSOC);

    if (!$card) {
        throw new Exception("Card não encontrado.");
    }

    // ================================
    // DELETE
    // ================================
    $stmt = $pdo->prepare("DELETE FROM dashboard WHERE id = ?");
    $stmt->execute([$id]);

    // ================================
    // LOG (PADRÃO NOVO)
    // ================================
    try {
        registrarLog(
            $pdo,
            $_SESSION['usuario_id'] ?? null,
            'dashboard',
            'DELETE',
            "Card '{$card['titulo']}' (ID {$id}) excluído"
        );
    } catch (Exception $e) {
        error_log("Erro ao registrar log: " . $e->getMessage());
    }

    // ================================
    // RESPOSTA
    // ================================
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
?>