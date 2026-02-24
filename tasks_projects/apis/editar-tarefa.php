<?php
header("Content-Type: application/json");
require_once __DIR__ . '/../config/connection.php';

try {

    $id = (int) $_POST['id'];

    $titulo = $_POST['titulo'];
    $descricao = $_POST['descricao'];
    $status = $_POST['status'];
    $prazo = $_POST['prazo'];

    $arquivoPath = null;

    if(isset($_FILES['arquivo']) && $_FILES['arquivo']['error'] === UPLOAD_ERR_OK){

        $pasta = "uploads/tarefas/";

        if (!is_dir($pasta)) {
            mkdir($pasta, 0777, true);
        }

        $ext = pathinfo($_FILES['arquivo']['name'], PATHINFO_EXTENSION);
        $nomeUnico = uniqid("tarefa_") . "." . $ext;
        $destino = $pasta . $nomeUnico;

        move_uploaded_file($_FILES['arquivo']['tmp_name'], $destino);

        $arquivoPath = $destino;
    }

    if($status === "Concluído"){
        $dataConclusao = date("Y-m-d");
    } else {
        $dataConclusao = null;
    }

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

    echo json_encode(["status" => "success"]);

} catch (Throwable $e) {

    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}