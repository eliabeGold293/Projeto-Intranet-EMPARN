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

    // Pasta base
    $uploadBase = __DIR__ . "/../uploads/doc/";

    if (!is_dir($uploadBase)) {
        mkdir($uploadBase, 0777, true);
    }

    $pdo->beginTransaction();

    $savedTopicsIds = [];

    foreach ($topics_meta as $index => $meta) {

        $nome = trim($meta["nome"] ?? "");
        $descricao = trim($meta["descricao"] ?? "");

        if ($nome === "") {
            continue;
        }

        // -------------------------------------------------
        // 1) SALVA O TÓPICO NO BANCO
        // -------------------------------------------------
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
        $savedTopicsIds[] = $topicoId;

        // LOG simples — tópico criado
        $descricaoLog = "Tópico '{$nome}' criado (ID $topicoId)";
        $stmtLog = $pdo->prepare("
            INSERT INTO log_acao (usuario_id, entidade, acao, descricao)
            VALUES (:usuario_id, 'documento_topico', 'INSERIR', :descricao)
        ");
        $stmtLog->execute([
            ":usuario_id" => 1, 
            ":descricao"  => $descricaoLog
        ]);

        // -------------------------------------------------
        // 2) CRIAR PASTA
        // -------------------------------------------------
        $topicFolder = $uploadBase . "topic_" . $topicoId . "/";

        if (!is_dir($topicFolder)) {
            mkdir($topicFolder, 0777, true);
        }

        // -------------------------------------------------
        // 3) PROCESSA ARQUIVOS
        // -------------------------------------------------
        $fieldName = "files_topic_" . $index;

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

            // caminho relativo que vai para o banco
            $caminhoRelativo = "uploads/doc/topic_{$topicoId}/{$unique}";

            // -------------------------------------------------
            // 4) SALVA ARQUIVO NO BANCO
            // -------------------------------------------------
            $stmtA = $pdo->prepare("
                INSERT INTO documento_arquivo
                (topico_id, nome_original, caminho_armazenado, tipo, tamanho)
                VALUES (:topico, :nome, :caminho, :tipo, :tamanho)
            ");

            $stmtA->execute([
                ":topico"  => $topicoId,
                ":nome"    => $orig,
                ":caminho" => $caminhoRelativo,
                ":tipo"    => $type,
                ":tamanho" => $size
            ]);

            // LOG simples — arquivo enviado
            $descricaoArquivo = "Arquivo '{$orig}' enviado (Tópico ID {$topicoId})";
            $stmtLog = $pdo->prepare("
                INSERT INTO log_acao (usuario_id, entidade, acao, descricao)
                VALUES (:usuario_id, 'documento_arquivo', 'INSERIR', :descricao)
            ");
            $stmtLog->execute([
                ":usuario_id" => 1,
                ":descricao"  => $descricaoArquivo
            ]);
        }
    }

    $pdo->commit();

    echo json_encode([
        "status" => "success",
        "message" => "Tópicos e arquivos salvos com sucesso!",
        "ids" => $savedTopicsIds
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
