<?php
header("Content-Type: application/json");

require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/../utils/log-action.php';
session_start();

$data = json_decode(file_get_contents("php://input"), true);

try {

    $id = (int) $data['id'];

    // ======================================
    // BUSCAR INFO ANTES DE EXCLUIR
    // ======================================
    $stmtInfo = $pdo->prepare("
        SELECT 
            t.titulo,
            p.titulo AS projeto
        FROM tarefa t
        LEFT JOIN projeto p ON p.id = t.projeto_id
        WHERE t.id = :id
    ");

    $stmtInfo->execute([":id" => $id]);
    $info = $stmtInfo->fetch(PDO::FETCH_ASSOC);

    if (!$info) {
        throw new Exception("Tarefa não encontrada");
    }

    $tituloTarefa = $info['titulo'] ?? "ID {$id}";
    $nomeProjeto  = $info['projeto'] ?? "Projeto desconhecido";

    // ======================================
    // DELETE
    // ======================================
    $stmt = $pdo->prepare("DELETE FROM tarefa WHERE id = :id");
    $stmt->execute([":id" => $id]);

    // ======================================
    // LOG
    // ======================================
    try {
        registrarLog(
            $pdo,
            $_SESSION['usuario_id'] ?? null,
            'tarefa',
            'DELETE',
            "Tarefa '{$tituloTarefa}' removida do projeto '{$nomeProjeto}'"
        );
    } catch (Exception $e) {
        error_log("Erro ao registrar log: " . $e->getMessage());
    }

    echo json_encode([
        "status" => "success"
    ]);

} catch (Throwable $e) {

    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}