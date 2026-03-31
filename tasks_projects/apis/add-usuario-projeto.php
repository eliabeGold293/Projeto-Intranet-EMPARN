<?php
require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/../utils/log-action.php';
session_start();

$data = json_decode(file_get_contents("php://input"), true);

$stmt = $pdo->prepare("
    INSERT INTO projeto_usuario (projeto_id, usuario_id, papel_id)
    VALUES (?, ?, ?)
");

try {

    // ======================================
    // INSERT
    // ======================================
    $stmt->execute([
        $data['projeto_id'],
        $data['usuario_id'],
        $data['papel_id']
    ]);

    // ======================================
    // BUSCAR INFO PARA LOG
    // ======================================
    $stmtInfo = $pdo->prepare("
        SELECT 
            (SELECT titulo FROM projeto WHERE id = :projeto_id) AS projeto,
            (SELECT nome FROM usuario WHERE id = :usuario_id) AS usuario,
            (SELECT nome FROM papel_projeto WHERE id = :papel_id) AS papel
    ");

    $stmtInfo->execute([
        ':projeto_id' => $data['projeto_id'],
        ':usuario_id' => $data['usuario_id'],
        ':papel_id'   => $data['papel_id']
    ]);

    $info = $stmtInfo->fetch(PDO::FETCH_ASSOC);

    // fallback seguro
    $projeto = $info['projeto'] ?? "ID {$data['projeto_id']}";
    $usuario = $info['usuario'] ?? "ID {$data['usuario_id']}";
    $papel   = $info['papel']   ?? "ID {$data['papel_id']}";

    // ======================================
    // LOG
    // ======================================
    try {
        registrarLog(
            $pdo,
            $_SESSION['usuario_id'] ?? null,
            'projeto_usuario',
            'CREATE',
            "Usuário '{$usuario}' vinculado ao projeto '{$projeto}' com papel '{$papel}'"
        );
    } catch (Exception $e) {
        error_log("Erro ao registrar log: " . $e->getMessage());
    }

    echo json_encode([
        "status" => "success"
    ]);

} catch (PDOException $e) {

    if ($e->getCode() === '23505') {
        echo json_encode([
            "status" => "error",
            "message" => "Usuário já vinculado a este projeto"
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => $e->getMessage()
        ]);
    }
}