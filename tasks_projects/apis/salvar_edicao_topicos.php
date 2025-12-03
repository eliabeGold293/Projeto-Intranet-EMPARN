<?php
header("Content-Type: application/json; charset=utf-8");
require_once "../config/connection.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

try {

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Use POST.");
    }

    if (!isset($_POST['topics_meta'])) {
        throw new Exception("Nenhum dado de tópico recebido.");
    }

    $topics_meta = json_decode($_POST['topics_meta'], true);

    if (!is_array($topics_meta)) {
        throw new Exception("Erro ao decodificar topics_meta.");
    }

    // Arquivos a excluir (opcional)
    $deleteFiles = $_POST['delete_files'] ?? [];

    // Pasta base
    $uploadBase = __DIR__ . "/../uploads/doc/";
    if (!is_dir($uploadBase)) {
        mkdir($uploadBase, 0777, true);
    }

    $pdo->beginTransaction();

    $resultIds = [];

    // -------------------------------------------------------
    // 1) EXCLUIR ARQUIVOS SOLICITADOS
    // -------------------------------------------------------
    if (!empty($deleteFiles) && is_array($deleteFiles)) {
        foreach ($deleteFiles as $fileId) {
            $stmt = $pdo->prepare("SELECT caminho_armazenado FROM documento_arquivo WHERE id = :id");
            $stmt->execute([":id" => $fileId]);
            $path = $stmt->fetchColumn();

            if ($path) {
                $full = __DIR__ . "/../" . $path;
                if (file_exists($full)) unlink($full);
            }

            $stmt = $pdo->prepare("DELETE FROM documento_arquivo WHERE id = :id");
            $stmt->execute([":id" => $fileId]);
        }
    }

    // -------------------------------------------------------
    // 2) PROCESSAR CADA TÓPICO
    // -------------------------------------------------------
    foreach ($topics_meta as $meta) {

        $key      = $meta["key"] ?? null;
        $dbId     = $meta["dbId"] ?? null;   // se existir → UPDATE
        $nome     = trim($meta["nome"] ?? "");
        $descricao = trim($meta["descricao"] ?? "");

        if ($nome === "") {
            continue; // ignora tópicos sem nome
        }

        // ------------------------------------------
        // 2.1) SE EXISTIR ID → UPDATE
        // ------------------------------------------
        if ($dbId) {
            $stmt = $pdo->prepare("
                UPDATE documento_topico
                SET nome = :nome,
                    descricao = :descricao,
                    data_modificacao = NOW()
                WHERE id = :id
            ");
            $stmt->execute([
                ":nome" => $nome,
                ":descricao" => $descricao,
                ":id" => $dbId
            ]);

            $topicoId = $dbId;
        }

        // ------------------------------------------
        // 2.2) SE NÃO EXISTE ID → INSERT
        // ------------------------------------------
        else {
            $stmt = $pdo->prepare("
                INSERT INTO documento_topico (nome, descricao)
                VALUES (:nome, :descricao)
                RETURNING id
            ");
            $stmt->execute([
                ":nome" => $nome,
                ":descricao" => $descricao
            ]);

            $topicoId = $stmt->fetchColumn();
        }

        $resultIds[] = $topicoId;

        // ------------------------------------------
        // Criar pasta do tópico
        // ------------------------------------------
        $topicFolder = $uploadBase . "topic_" . $topicoId . "/";
        if (!is_dir($topicFolder)) {
            mkdir($topicFolder, 0777, true);
        }

        // ------------------------------------------
        // 3) PROCESSAR ARQUIVOS NOVOS
        // ------------------------------------------
        $fieldName = "files_topic_" . $key;

        if (!isset($_FILES[$fieldName])) {
            continue;
        }

        $files = $_FILES[$fieldName];

        for ($i = 0; $i < count($files["name"]); $i++) {

            $orig = basename($files["name"][$i]);
            $tmp  = $files["tmp_name"][$i];
            $type = $files["type"][$i];
            $size = $files["size"][$i];
            $err  = $files["error"][$i];

            if ($err !== UPLOAD_ERR_OK) {
                throw new Exception("Erro ao enviar arquivo: $orig");
            }

            if (!is_uploaded_file($tmp)) {
                throw new Exception("Arquivo temporário inválido: $orig");
            }

            // caminho final
            $unique = uniqid("f_", true) . "_" . $orig;
            $dest = $topicFolder . $unique;

            if (!move_uploaded_file($tmp, $dest)) {
                throw new Exception("Falha ao mover arquivo: $orig");
            }

            $caminhoRelativo = "uploads/doc/topic_{$topicoId}/{$unique}";

            // Salvar na tabela documento_arquivo
            $stmtA = $pdo->prepare("
                INSERT INTO documento_arquivo
                (topico_id, nome_original, caminho_armazenado, tipo, tamanho)
                VALUES (:topico, :nome, :caminho, :tipo, :tamanho)
            ");

            $stmtA->execute([
                ":topico" => $topicoId,
                ":nome" => $orig,
                ":caminho" => $caminhoRelativo,
                ":tipo" => $type,
                ":tamanho" => $size
            ]);
        }
    }

    $pdo->commit();

    echo json_encode([
        "status" => "success",
        "message" => "Edição salva com sucesso!",
        "ids" => $resultIds
    ]);

} catch (Exception $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
?>