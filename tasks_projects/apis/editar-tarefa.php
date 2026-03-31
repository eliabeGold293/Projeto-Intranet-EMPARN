<?php
header("Content-Type: application/json");

require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/../utils/log-action.php';
session_start();

try {

    $id = (int) $_POST['id'];

    $titulo = $_POST['titulo'];
    $descricao = $_POST['descricao'];
    $status = $_POST['status'];
    $prazo = $_POST['prazo'];

    $arquivoPath = null;
    $nomeArquivo = null;

    // ======================================
    // BUSCAR DADOS ANTIGOS
    // ======================================
    $stmtAntes = $pdo->prepare("
        SELECT t.*, p.titulo AS projeto
        FROM tarefa t
        JOIN projeto p ON p.id = t.projeto_id
        WHERE t.id = :id
    ");
    $stmtAntes->execute([":id" => $id]);

    $antes = $stmtAntes->fetch(PDO::FETCH_ASSOC);

    if (!$antes) {
        throw new Exception("Tarefa não encontrada.");
    }

    // ======================================
    // UPLOAD (opcional)
    // ======================================
    if(isset($_FILES['arquivo']) && $_FILES['arquivo']['error'] === UPLOAD_ERR_OK){

        $pasta = "uploads/tarefas/";

        if (!is_dir($pasta)) {
            mkdir($pasta, 0777, true);
        }

        $nomeOriginal = $_FILES['arquivo']['name'];
        $nomeArquivo = $nomeOriginal;

        $ext = pathinfo($nomeOriginal, PATHINFO_EXTENSION);
        $nomeUnico = uniqid("tarefa_") . "." . $ext;
        $destino = $pasta . $nomeUnico;

        move_uploaded_file($_FILES['arquivo']['tmp_name'], $destino);

        $arquivoPath = $destino;
    }

    // ======================================
    // DATA CONCLUSÃO
    // ======================================
    if($status === "Concluído"){
        $dataConclusao = date("Y-m-d");
    } else {
        $dataConclusao = null;
    }

    // ======================================
    // UPDATE
    // ======================================
    if($arquivoPath){

        $sql = "UPDATE tarefa SET
                titulo = :titulo,
                descricao = :descricao,
                status = :status,
                prazo = :prazo,
                arquivo = :arquivo,
                data_conclusao = :data_conclusao,
                data_modificacao = NOW()
                WHERE id = :id";

        $params = [
            ":arquivo" => $arquivoPath
        ];

    } else {

        $sql = "UPDATE tarefa SET
                titulo = :titulo,
                descricao = :descricao,
                status = :status,
                prazo = :prazo,
                data_conclusao = :data_conclusao,
                data_modificacao = NOW()
                WHERE id = :id";

        $params = [];
    }

    $stmt = $pdo->prepare($sql);

    $stmt->execute(array_merge($params, [
        ":titulo" => $titulo,
        ":descricao" => $descricao,
        ":status" => $status,
        ":prazo" => $prazo,
        ":data_conclusao" => $dataConclusao,
        ":id" => $id
    ]));

    // ======================================
    // MONTAR DESCRIÇÃO DO LOG
    // ======================================
    $mudancas = [];

    if ($antes['titulo'] !== $titulo) {
        $mudancas[] = "título: '{$antes['titulo']}' → '{$titulo}'";
    }

    if ($antes['status'] !== $status) {
        $mudancas[] = "status: '{$antes['status']}' → '{$status}'";
    }

    if ($antes['prazo'] != $prazo) {
        $mudancas[] = "prazo alterado";
    }

    if ($nomeArquivo) {
        $mudancas[] = "novo anexo: '{$nomeArquivo}'";
    }

    $detalhes = !empty($mudancas) ? " (" . implode(", ", $mudancas) . ")" : "";

    // ======================================
    // LOG
    // ======================================
    try {
        registrarLog(
            $pdo,
            $_SESSION['usuario_id'] ?? null,
            'tarefa',
            'UPDATE',
            "Tarefa '{$titulo}' atualizada no projeto '{$antes['projeto']}'{$detalhes}"
        );
    } catch (Exception $e) {
        error_log("Erro ao registrar log: " . $e->getMessage());
    }

    echo json_encode(["status" => "success"]);

} catch (Throwable $e) {

    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}