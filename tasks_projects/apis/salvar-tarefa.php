<?php
header("Content-Type: application/json");

require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/../utils/log-action.php';
session_start();

try {

    if (!isset($_POST['titulo']) || empty(trim($_POST['titulo']))) {
        echo json_encode([
            "status" => "error",
            "message" => "Título é obrigatório."
        ]);
        exit;
    }

    $projeto_id = $_POST['projeto_id'];
    $titulo     = trim($_POST['titulo']);
    $descricao  = $_POST['descricao'] ?? null;
    $status     = $_POST['status'] ?? 'Em andamento';
    $prazo      = !empty($_POST['prazo']) ? $_POST['prazo'] : null;

    $arquivoPath = null;

    // ======================================
    // UPLOAD (opcional)
    // ======================================
    if (isset($_FILES['arquivo']) && $_FILES['arquivo']['error'] === UPLOAD_ERR_OK) {

        $pasta = "uploads/tarefas/";

        if (!is_dir($pasta)) {
            mkdir($pasta, 0777, true);
        }

        $nomeOriginal = $_FILES['arquivo']['name'];
        $ext = pathinfo($nomeOriginal, PATHINFO_EXTENSION);

        $nomeUnico = uniqid("tarefa_") . "." . $ext;
        $destino = $pasta . $nomeUnico;

        move_uploaded_file($_FILES['arquivo']['tmp_name'], $destino);

        $arquivoPath = $destino;
    }

    // ======================================
    // DATA CONCLUSÃO AUTOMÁTICA
    // ======================================
    $dataConclusao = null;
    if ($status === "Concluído") {
        $dataConclusao = date("Y-m-d");
    }

    // ======================================
    // INSERT
    // ======================================
    $sql = "INSERT INTO tarefa 
        (projeto_id, titulo, descricao, status, arquivo, prazo, data_conclusao)
        VALUES 
        (:projeto_id, :titulo, :descricao, :status, :arquivo, :prazo, :data_conclusao)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":projeto_id"     => $projeto_id,
        ":titulo"         => $titulo,
        ":descricao"      => $descricao,
        ":status"         => $status,
        ":arquivo"        => $arquivoPath,
        ":prazo"          => $prazo,
        ":data_conclusao" => $dataConclusao
    ]);

    // ======================================
    // BUSCAR INFO PARA LOG
    // ======================================
    $stmtInfo = $pdo->prepare("
        SELECT titulo FROM projeto WHERE id = :id
    ");
    $stmtInfo->execute([':id' => $projeto_id]);

    $projetoNome = $stmtInfo->fetchColumn() ?? "ID {$projeto_id}";

    $infoArquivo = $nomeOriginal ? " | Anexo: '{$nomeOriginal}'" : "";

    // ======================================
    // LOG
    // ======================================
    try {
        registrarLog(
            $pdo,
            $_SESSION['usuario_id'] ?? null,
            'tarefa',
            'CREATE',
            "Tarefa '{$titulo}' criada no projeto '{$projetoNome}' (status: {$status}){$infoArquivo}"
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