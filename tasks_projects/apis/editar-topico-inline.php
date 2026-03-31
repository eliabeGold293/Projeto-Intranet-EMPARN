<?php
header("Content-Type: application/json");

require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/../utils/log-action.php';
session_start();

$input = json_decode(file_get_contents("php://input"), true);

$id = $input['id'] ?? null;
$nome = $input['nome'] ?? null;

if (!$id || !$nome) {
    echo json_encode(["status" => "error", "message" => "Dados inválidos"]);
    exit;
}

try {

    // ---------------------------
    // BUSCAR NOME ANTIGO
    // ---------------------------
    $stmtAntes = $pdo->prepare("SELECT nome FROM documento_topico WHERE id = :id");
    $stmtAntes->execute([":id" => $id]);

    $antes = $stmtAntes->fetch(PDO::FETCH_ASSOC);

    if (!$antes) {
        throw new Exception("Tópico não encontrado");
    }

    $nomeAntigo = $antes['nome'];

    // ---------------------------
    // UPDATE
    // ---------------------------
    $sql = "UPDATE documento_topico SET nome = :nome WHERE id = :id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":nome" => $nome,
        ":id" => $id
    ]);

    // ---------------------------
    // BUSCAR USUÁRIO
    // ---------------------------
    $stmtUser = $pdo->prepare("SELECT nome FROM usuario WHERE id = :id");
    $stmtUser->execute([':id' => $_SESSION['usuario_id']]);

    $usuarioLog = $stmtUser->fetchColumn() ?? "Usuário desconhecido";

    // ---------------------------
    // LOG
    // ---------------------------
    try {
        registrarLog(
            $pdo,
            $_SESSION['usuario_id'] ?? null,
            'documento_topico',
            'UPDATE',
            "Tópico '{$nomeAntigo}' foi renomeado para '{$nome}' por '{$usuarioLog}' (ID {$id}) em Documentos institucionais"
        );
    } catch (Exception $e) {
        error_log("Erro ao registrar log: " . $e->getMessage());
    }

    echo json_encode(["status" => "success"]);

} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}