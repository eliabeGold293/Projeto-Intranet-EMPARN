<?php
require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/../utils/log-action.php';
session_start();

$data = json_decode(file_get_contents("php://input"), true);

$projetoId = $data['projeto_id'] ?? null;
$usuarioId = $data['usuario_id'] ?? null;

if (!$projetoId || !$usuarioId) {
    echo json_encode([
        "status" => "error",
        "message" => "Dados inválidos"
    ]);
    exit;
}

try {

    // ======================================
    // BUSCAR INFO PARA LOG (ANTES DO DELETE)
    // ======================================
    $stmtInfo = $pdo->prepare("
        SELECT 
            (SELECT titulo FROM projeto WHERE id = :projeto_id) AS projeto,
            (SELECT nome FROM usuario WHERE id = :usuario_id) AS usuario
    ");

    $stmtInfo->execute([
        ':projeto_id' => $projetoId,
        ':usuario_id' => $usuarioId
    ]);

    $info = $stmtInfo->fetch(PDO::FETCH_ASSOC);

    $projeto = $info['projeto'] ?? "ID {$projetoId}";
    $usuario = $info['usuario'] ?? "ID {$usuarioId}";

    // ======================================
    // DELETE
    // ======================================
    $stmt = $pdo->prepare("
        DELETE FROM projeto_usuario
        WHERE projeto_id = ?
        AND usuario_id = ?
    ");

    $stmt->execute([$projetoId, $usuarioId]);

    // ======================================
    // LOG
    // ======================================
    try {
        registrarLog(
            $pdo,
            $_SESSION['usuario_id'] ?? null,
            'projeto_usuario',
            'DELETE',
            "Usuário '{$usuario}' removido do projeto '{$projeto}'"
        );
    } catch (Exception $e) {
        error_log("Erro ao registrar log: " . $e->getMessage());
    }

    echo json_encode([
        "status" => "success"
    ]);

} catch (Exception $e) {

    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}