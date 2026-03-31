<?php
header("Content-Type: application/json");

require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/../utils/log-action.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["status" => "error", "message" => "Método inválido"]);
    exit;
}

try {

    $input = json_decode(file_get_contents("php://input"), true);

    $id = $input["id"] ?? null;
    $campo = $input["campo"] ?? null;
    $valor = $input["valor"] ?? null;

    if (!$id || !$campo) {
        throw new Exception("Dados inválidos");
    }

    // ======================================
    // SEGURANÇA
    // ======================================
    $camposPermitidos = ["titulo", "cor", "link"];

    if (!in_array($campo, $camposPermitidos)) {
        throw new Exception("Campo não permitido");
    }

    // ======================================
    // BUSCAR DADOS ANTES
    // ======================================
    $stmtAntes = $pdo->prepare("SELECT titulo, $campo FROM dashboard WHERE id = :id");
    $stmtAntes->execute([":id" => $id]);

    $antes = $stmtAntes->fetch(PDO::FETCH_ASSOC);

    if (!$antes) {
        throw new Exception("Card não encontrado");
    }

    $valorAntigo = $antes[$campo] ?? 'vazio';

    // ======================================
    // UPDATE
    // ======================================
    $sql = "UPDATE dashboard SET $campo = :valor WHERE id = :id";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ":valor" => $valor,
        ":id" => $id
    ]);

    // ======================================
    // BUSCAR USUÁRIO
    // ======================================
    $stmtUser = $pdo->prepare("SELECT nome FROM usuario WHERE id = :id");
    $stmtUser->execute([':id' => $_SESSION['usuario_id']]);

    $usuarioLog = $stmtUser->fetchColumn() ?? "Usuário desconhecido";

    // ======================================
    // LOG
    // ======================================
    try {
        registrarLog(
            $pdo,
            $_SESSION['usuario_id'] ?? null,
            'dashboard',
            'UPDATE',
            "Card '{$antes['titulo']}' teve o campo '{$campo}' alterado de '{$valorAntigo}' para '{$valor}' por '{$usuarioLog}'"
        );
    } catch (Exception $e) {
        error_log("Erro ao registrar log: " . $e->getMessage());
    }

    echo json_encode([
        "status" => "success",
        "message" => "Atualizado com sucesso"
    ]);

} catch (Exception $e) {

    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}