<?php
require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/../utils/log-action.php';
session_start();

$data = json_decode(file_get_contents("php://input"), true);

$projetoId = $data['id'] ?? null;
$descricao = $data['valor'] ?? null;

if (!$projetoId) {
    echo json_encode([
        "status" => "error",
        "message" => "ID do projeto inválido"
    ]);
    exit;
}

try {

    // ======================================
    // BUSCAR INFO ANTES DO UPDATE (LOG)
    // ======================================
    $stmtInfo = $pdo->prepare("
        SELECT titulo, descricao 
        FROM projeto 
        WHERE id = :id
    ");

    $stmtInfo->execute([':id' => $projetoId]);
    $info = $stmtInfo->fetch(PDO::FETCH_ASSOC);

    if (!$info) {
        echo json_encode([
            "status" => "error",
            "message" => "Projeto não encontrado"
        ]);
        exit;
    }

    $tituloProjeto = $info['titulo'];
    $descricaoAntiga = $info['descricao'] ?? '';

    // ======================================
    // UPDATE
    // ======================================
    $stmt = $pdo->prepare("
        UPDATE projeto
        SET descricao = :descricao
        WHERE id = :id
    ");

    $stmt->execute([
        ':descricao' => $descricao,
        ':id' => $projetoId
    ]);

    // ======================================
    // LOG
    // ======================================
    try {
        registrarLog(
            $pdo,
            $_SESSION['usuario_id'] ?? null,
            'projeto',
            'UPDATE',
            "Descrição do projeto '{$tituloProjeto}' alterada"
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