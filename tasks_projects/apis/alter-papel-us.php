<?php
require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/../utils/log-action.php';
session_start();

$data = json_decode(file_get_contents("php://input"), true);

$projetoId = $data["projeto_id"] ?? null;
$usuarioId = $data["usuario_id"] ?? null;
$papelNovoId = $data["papel_id"] ?? null;

if (!$projetoId || !$usuarioId || !$papelNovoId) {
    echo json_encode([
        "status" => "error",
        "message" => "Dados inválidos"
    ]);
    exit;
}

try {

    // ======================================
    // BUSCAR DADOS ANTES (papel atual)
    // ======================================
    $stmtAntes = $pdo->prepare("
        SELECT 
            pu.papel_id,
            (SELECT titulo FROM projeto WHERE id = pu.projeto_id) AS projeto,
            (SELECT nome FROM usuario WHERE id = pu.usuario_id) AS usuario,
            (SELECT nome FROM papel_projeto WHERE id = pu.papel_id) AS papel_atual
        FROM projeto_usuario pu
        WHERE pu.projeto_id = :projeto_id
          AND pu.usuario_id = :usuario_id
    ");

    $stmtAntes->execute([
        ':projeto_id' => $projetoId,
        ':usuario_id' => $usuarioId
    ]);

    $antes = $stmtAntes->fetch(PDO::FETCH_ASSOC);

    if (!$antes) {
        throw new Exception("Vínculo não encontrado.");
    }

    $projeto = $antes['projeto'] ?? "ID {$projetoId}";
    $usuario = $antes['usuario'] ?? "ID {$usuarioId}";
    $papelAntigo = $antes['papel_atual'] ?? "ID {$antes['papel_id']}";

    // ======================================
    // UPDATE
    // ======================================
    $stmt = $pdo->prepare("
        UPDATE projeto_usuario
        SET papel_id = :papel_id
        WHERE projeto_id = :projeto_id
          AND usuario_id = :usuario_id
    ");

    $stmt->execute([
        ":papel_id"   => $papelNovoId,
        ":projeto_id" => $projetoId,
        ":usuario_id" => $usuarioId
    ]);

    // ======================================
    // BUSCAR NOVO PAPEL
    // ======================================
    $stmtNovo = $pdo->prepare("
        SELECT nome FROM papel_projeto WHERE id = :papel_id
    ");
    $stmtNovo->execute([':papel_id' => $papelNovoId]);

    $papelNovo = $stmtNovo->fetchColumn() ?? "ID {$papelNovoId}";

    // ======================================
    // LOG
    // ======================================
    try {
        registrarLog(
            $pdo,
            $_SESSION['usuario_id'] ?? null,
            'projeto_usuario',
            'UPDATE',
            "Usuário '{$usuario}' no projeto '{$projeto}' teve papel alterado de '{$papelAntigo}' para '{$papelNovo}'"
        );
    } catch (Exception $e) {
        error_log("Erro ao registrar log: " . $e->getMessage());
    }

    echo json_encode([
        "status" => "success",
        "linhas_afetadas" => $stmt->rowCount()
    ]);

} catch (Exception $e) {

    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}