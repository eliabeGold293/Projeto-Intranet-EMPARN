<?php
header("Content-Type: application/json; charset=utf-8");
require_once __DIR__ . '/../config/connection.php';
session_start(); // <-- necessário para pegar usuario_id

error_reporting(E_ALL);
ini_set('display_errors', 1);

try {

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Método inválido. Use POST.");
    }

    if (!isset($_POST['id'])) {
        throw new Exception("ID do tópico não enviado.");
    }

    $id         = intval($_POST['id']);
    $nome       = trim($_POST['nome'] ?? '');
    $descricao  = trim($_POST['descricao'] ?? '');

    if ($nome === '') {
        throw new Exception("O nome do tópico é obrigatório.");
    }

    // Pasta base: uploads/doc/topic_ID/
    $basePath = realpath(__DIR__ . "/../uploads/doc/");
    if (!$basePath) {
        mkdir(__DIR__ . "/../uploads/doc/", 0777, true);
        $basePath = __DIR__ . "/../uploads/doc/";
    }

    $topicFolder = $basePath . "/topic_" . $id . "/";
    if (!is_dir($topicFolder)) {
        mkdir($topicFolder, 0777, true);
    }

    $pdo->beginTransaction();

    // -------------------------------------
    // 1) ATUALIZAR TÓPICO
    // -------------------------------------
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
        ":id" => $id
    ]);

    // -------------------------------------
    // 2) UPLOAD DE NOVOS ARQUIVOS
    // -------------------------------------
    if (isset($_FILES['novos_arquivos'])) {

        $arquivos = $_FILES['novos_arquivos'];

        for ($i = 0; $i < count($arquivos['name']); $i++) {

            if ($arquivos['error'][$i] !== UPLOAD_ERR_OK) {
                continue; // ignora arquivos vazios
            }

            $orig  = basename($arquivos['name'][$i]);
            $tmp   = $arquivos['tmp_name'][$i];
            $type  = $arquivos['type'][$i];
            $size  = $arquivos['size'][$i];

            if (!is_uploaded_file($tmp)) {
                throw new Exception("Falha ao validar arquivo recebido.");
            }

            // Gera nome único
            $unique = uniqid('f_', true) . "_" . $orig;
            $dest = $topicFolder . $unique;

            if (!move_uploaded_file($tmp, $dest)) {
                throw new Exception("Falha ao salvar arquivo: $orig");
            }

            // Caminho relativo para salvar no banco
            $relative = "uploads/doc/topic_{$id}/{$unique}";

            // INSERE no banco
            $stmtA = $pdo->prepare("
                INSERT INTO documento_arquivo
                (topico_id, nome_original, caminho_armazenado, tipo, tamanho)
                VALUES (:topico, :nome, :caminho, :tipo, :tamanho)
            ");
            $stmtA->execute([
                ":topico" => $id,
                ":nome" => $orig,
                ":caminho" => $relative,
                ":tipo" => $type,
                ":tamanho" => $size
            ]);
        }
    }

    $pdo->commit();

    // -------------------------------------
    // 3) REGISTRA LOG da alteração
    // -------------------------------------
    $stmtLog = $pdo->prepare("
        INSERT INTO log_acao (usuario_id, entidade, acao, descricao)
        VALUES (:usuario_id, 'documento_topico', 'ATUALIZAR', :descricao)
    ");

    $descricaoLog = "Tópico '{$nome}' (ID {$id}) atualizado.";

    $stmtLog->execute([
        ":usuario_id" => $_SESSION['usuario_id'] ?? null,
        ":descricao"  => $descricaoLog
    ]);

    echo json_encode([
        "status" => "success",
        "message" => "Alterações salvas com sucesso!",
        "id" => $id
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
