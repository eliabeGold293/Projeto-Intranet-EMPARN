<?php
header("Content-Type: application/json");

// segurança básica
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/connection.php';

try {

    if (!isset($_GET['projeto_id']) || empty($_GET['projeto_id'])) {
        echo json_encode([]);
        exit;
    }

    $projeto_id = (int) $_GET['projeto_id'];

    $sql = "SELECT 
                id,
                titulo,
                descricao,
                status,
                arquivo,
                prazo,
                data_conclusao,
                data_criacao
            FROM tarefa
            WHERE projeto_id = :projeto_id
            ORDER BY data_criacao DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":projeto_id" => $projeto_id
    ]);

    $tarefas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($tarefas);

} catch (Throwable $e) {

    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}