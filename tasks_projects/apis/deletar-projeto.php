<?php
header("Content-Type: application/json");
require_once __DIR__ . '/../config/connection.php';

try {

    $input = json_decode(file_get_contents("php://input"), true);

    if (!$input) {
        throw new Exception("JSON inválido");
    }

    if (!isset($input['projeto_id'])) {
        throw new Exception("ID do projeto não informado");
    }

    $projetoId = intval($input['projeto_id']);

    // Verifica se o projeto existe
    $check = $pdo->prepare("SELECT id FROM projeto WHERE id = :id");
    $check->bindParam(":id", $projetoId);
    $check->execute();

    if ($check->rowCount() === 0) {
        throw new Exception("Projeto não encontrado");
    }

    // Deleta o projeto
    $stmt = $pdo->prepare("DELETE FROM projeto WHERE id = :id");
    $stmt->bindParam(":id", $projetoId);
    $stmt->execute();

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
