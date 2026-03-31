<?php
require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/../utils/log-action.php'; // <-- IMPORTANTE
session_start();

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

$id = $data['id'] ?? null;
$campo = $data['campo'] ?? null;
$valor = $data['valor'] ?? null;

$permitidos = ["titulo","status","data_inicio","data_fim"];

if (!$id || !in_array($campo, $permitidos)) {
    echo json_encode([
        "status"=>"error",
        "message"=>"Campo inválido"
    ]);
    exit;
}

try {

    // -------------------------------------
    // 1) BUSCAR VALOR ANTIGO 
    // -------------------------------------
    $stmtOld = $pdo->prepare("SELECT $campo, titulo FROM projeto WHERE id = :id");
    $stmtOld->execute([":id"=>$id]);
    $projeto = $stmtOld->fetch(PDO::FETCH_ASSOC);

    if (!$projeto) {
        throw new Exception("Projeto não encontrado");
    }

    $valorAntigo = $projeto[$campo];
    $tituloProjeto = $projeto['titulo'];

    // -------------------------------------
    // 2) UPDATE
    // -------------------------------------
    $sql = "UPDATE projeto
            SET $campo = :valor,
                data_modificacao = NOW()
            WHERE id = :id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":valor"=>$valor,
        ":id"=>$id
    ]);

    // -------------------------------------
    // 3) LOG INTELIGENTE 
    // -------------------------------------
    try {

        $descricao = "Projeto '{$tituloProjeto}' (ID {$id}) atualizado: {$campo} alterado";

        // só mostra mudança se realmente mudou
        if ($valorAntigo != $valor) {
            $descricao .= " de '{$valorAntigo}' para '{$valor}'";
        }

        registrarLog(
            $pdo,
            $_SESSION['usuario_id'] ?? null,
            'projeto',
            'UPDATE',
            $descricao
        );

    } catch (Exception $e) {
        error_log("Erro ao registrar log: " . $e->getMessage());
    }

    echo json_encode([
        "status"=>"success",
        "message"=>"Atualizado com sucesso"
    ]);

} catch (Exception $e){

    echo json_encode([
        "status"=>"error",
        "message"=>$e->getMessage()
    ]);
}