<?php
header("Content-Type: application/json");
require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/../utils/log-action.php';
session_start();

try {

    $input = json_decode(file_get_contents("php://input"), true);

    if (!$input) {
        throw new Exception("JSON inválido");
    }

    if (!isset($input['projeto_id'])) {
        throw new Exception("ID do projeto não informado");
    }

    $projetoId = intval($input['projeto_id']);

    // Buscar dados antes de deletar
    $stmtNome = $pdo->prepare("SELECT titulo FROM projeto WHERE id = :id");
    $stmtNome->execute([":id"=>$projetoId]);
    $projeto = $stmtNome->fetch(PDO::FETCH_ASSOC);

    if (!$projeto) {
        throw new Exception("Projeto não encontrado");
    }

    // Deletar
    $stmt = $pdo->prepare("DELETE FROM projeto WHERE id = :id");
    $stmt->execute([":id"=>$projetoId]);

    // LOG PADRÃO
    try {
        registrarLog(
            $pdo,
            $_SESSION['usuario_id'] ?? null,
            'projeto',
            'DELETE',
            "Projeto '{$projeto['titulo']}' (ID {$projetoId}) excluído"
        );
    } catch (Exception $e) {
        error_log("Erro ao registrar log: " . $e->getMessage());
    }

    echo json_encode([
        "status" => "success",
        "message" => "Projeto deletado com sucesso"
    ]);

} catch (Exception $e) {

    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}