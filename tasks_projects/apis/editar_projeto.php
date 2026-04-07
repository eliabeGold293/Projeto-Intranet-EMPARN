<?php
require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/../utils/log-action.php';

session_start();

header("Content-Type: application/json");

// GARANTE ERROS COMO EXCEPTION
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

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

    // =====================================
    // VALIDAÇÃO DE DATA (SE FOR DATA)
    // =====================================
    if (in_array($campo, ['data_inicio', 'data_fim'])) {

        if (!preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $valor)) {
            throw new Exception("Não foi possível alterar pois não está escrito da devida forma: DD/MM/AAAA");
        }

        $dataFormatada = DateTime::createFromFormat('d/m/Y', $valor);

        if (!$dataFormatada || $dataFormatada->format('d/m/Y') !== $valor) {
            throw new Exception("Não foi possível alterar pois não está escrito da devida forma: DD/MM/AAAA");
        }

        // Converte para formato do banco
        $valor = $dataFormatada->format('Y-m-d');
    }

    // =====================================
    // 1) BUSCAR VALOR ANTIGO
    // =====================================
    $stmtOld = $pdo->prepare("SELECT $campo, titulo FROM projeto WHERE id = :id");
    $stmtOld->execute([":id"=>$id]);
    $projeto = $stmtOld->fetch(PDO::FETCH_ASSOC);

    if (!$projeto) {
        throw new Exception("Projeto não encontrado");
    }

    $valorAntigo = $projeto[$campo];
    $tituloProjeto = $projeto['titulo'];

    // =====================================
    // 2) UPDATE
    // =====================================
    $stmt = $pdo->prepare("
        UPDATE projeto
        SET $campo = :valor,
            data_modificacao = NOW()
        WHERE id = :id
    ");

    $stmt->execute([
        ":valor"=>$valor,
        ":id"=>$id
    ]);

    // =====================================
    // 3) LOG
    // =====================================
    try {

        $descricao = "Projeto '{$tituloProjeto}' (ID {$id}) atualizado: {$campo} alterado";

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

    } catch (Throwable $e) {
        error_log("Erro ao registrar log: " . $e->getMessage());
    }

    echo json_encode([
        "status"=>"success",
        "message"=>"Atualizado com sucesso"
    ]);

} catch (Throwable $e){

    // TRADUZ ERRO SQL PRA MENSAGEM AMIGÁVEL
    if (str_contains($e->getMessage(), 'datetime') || str_contains($e->getMessage(), 'date')) {
        $mensagem = "Não foi possível alterar pois não está escrito da devida forma: DD/MM/AAAA";
    } else {
        $mensagem = $e->getMessage();
    }

    echo json_encode([
        "status"=>"error",
        "message"=>$mensagem
    ]);
}