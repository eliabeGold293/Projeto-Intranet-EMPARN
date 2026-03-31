<?php
header("Content-Type: application/json");

require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/../utils/log-action.php'; // <-- IMPORTANTE
session_start();

// permitir POST apenas
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "status" => "error",
        "message" => "Método não permitido"
    ]);
    exit;
}

try {

    // pegar JSON do body
    $input = json_decode(file_get_contents("php://input"), true);

    if (!$input) {
        throw new Exception("JSON inválido");
    }

    $titulo = $input["titulo"] ?? null;
    $descricao = $input["descricao"] ?? null;
    $data_inicio = $input["data_inicio"] ?? null;
    $data_fim = $input["data_fim"] ?? null;

    if (!$titulo) {
        throw new Exception("Título é obrigatório");
    }

    // INSERT
    $usuario_id = $_SESSION['usuario_id'] ?? null;

    if (!$usuario_id) {
        throw new Exception("Usuário não autenticado");
    }

    $sql = "INSERT INTO projeto 
        (titulo, descricao, data_inicio, data_fim, criado_por)
        VALUES (:titulo, :descricao, :data_inicio, :data_fim, :criado_por)
        RETURNING id";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ":titulo" => $titulo,
        ":descricao" => $descricao,
        ":data_inicio" => $data_inicio,
        ":data_fim" => $data_fim,
        ":criado_por" => $usuario_id
    ]);

    $novoId = $stmt->fetchColumn();

    // -------------------------------------
    // LOG PADRONIZADO
    // -------------------------------------
    try {
        registrarLog(
            $pdo,
            $_SESSION['usuario_id'] ?? null,
            'projeto',
            'CREATE',
            "Projeto '{$titulo}' criado (ID {$novoId})"
        );
    } catch (Exception $e) {
        error_log("Erro ao registrar log: " . $e->getMessage());
    }

    // opcional debug
    $dbAtual = $pdo->query("SELECT current_database()")->fetchColumn();

    echo json_encode([
        "status" => "success",
        "message" => "Projeto criado com sucesso",
        "id" => $novoId,
        "debug_db" => $dbAtual
    ]);

    exit;
} catch (Exception $e) {

    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
