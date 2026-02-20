<?php
header("Content-Type: application/json");

require_once __DIR__ . '/../config/connection.php';

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

    // 📎 Upload opcional
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

    // ⏱ Data conclusão automática
    $dataConclusao = null;
    if ($status === "Concluído") {
        $dataConclusao = date("Y-m-d");
    }

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

    echo json_encode([
        "status" => "success"
    ]);

} catch (Exception $e) {

    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}