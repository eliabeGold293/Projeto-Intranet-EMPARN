<?php
header("Content-Type: application/json; charset=utf-8");

require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/../utils/log-action.php';
session_start();

try {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        throw new Exception("Use POST.");
    }

    if (!isset($_POST['topico_id']) || !isset($_FILES['arquivo'])) {
        throw new Exception("Dados incompletos.");
    }

    $topicoId = intval($_POST['topico_id']);
    $file = $_FILES['arquivo'];

    if ($topicoId <= 0) {
        throw new Exception("ID do tópico inválido.");
    }

    // Verificar se tópico existe
    $stmtCheck = $pdo->prepare("SELECT id FROM documento_topico WHERE id = :id");
    $stmtCheck->execute([':id' => $topicoId]);
    $topicoExistente = $stmtCheck->fetchColumn();

    if (!$topicoExistente) {
        throw new Exception("Tópico não encontrado.");
    }

    // Validar arquivo
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("Erro no upload do arquivo.");
    }

    $orig = basename($file['name']);
    $tmp  = $file['tmp_name'];
    $type = $file['type'];
    $size = $file['size'];

    // Salvar no servidor
    $folder = __DIR__ . "/../uploads/doc/";
    if (!is_dir($folder)) mkdir($folder, 0777, true);

    $novoNome = uniqid("f_") . "_" . $orig;
    $destino = $folder . $novoNome;

    if (!move_uploaded_file($tmp, $destino)) {
        throw new Exception("Erro ao salvar arquivo.");
    }

    $caminhoRelativo = "uploads/doc/" . $novoNome;

    // Inserir no banco
    $stmtInsert = $pdo->prepare("
        INSERT INTO documento_arquivo
        (topico_id, nome_original, caminho_armazenado, tipo, tamanho, enviado_por, data_upload)
        VALUES (:topico_id, :nome_original, :caminho_armazenado, :tipo, :tamanho, :enviado_por, NOW())
    ");

    $stmtInsert->execute([
        ':topico_id' => $topicoId,
        ':nome_original' => $orig,
        ':caminho_armazenado' => $caminhoRelativo,
        ':tipo' => $type,
        ':tamanho' => $size,
        ':enviado_por' => $_SESSION['usuario_id'] ?? null
    ]);

    $arquivoId = $pdo->lastInsertId();

    // Log de ação
    try {
        $usuarioLog = $_SESSION['usuario_id'] ? $pdo->query("SELECT nome FROM usuario WHERE id = {$_SESSION['usuario_id']}")->fetchColumn() : "Usuário desconhecido";

        // Pegar o nome do tópico
        $stmtNomeTopico = $pdo->prepare("SELECT nome FROM documento_topico WHERE id = :id");
        $stmtNomeTopico->execute([':id' => $topicoId]);
        $nomeTopico = $stmtNomeTopico->fetchColumn() ?: "Tópico desconhecido";

        registrarLog(
            $pdo,
            $_SESSION['usuario_id'] ?? null,
            'documento_arquivo',
            'UPDATE',
            "Arquivo '{$orig}' (ID {$arquivoId}) adicionado ao Tópico '{$nomeTopico}' (ID {$topicoId}) por '{$usuarioLog}'"
        );

    } catch (Exception $e) {
        error_log("Erro ao registrar log: " . $e->getMessage());
    }

    echo json_encode([
        "status" => "success",
        "message" => "Arquivo adicionado com sucesso.",
        "arquivo_id" => $arquivoId
    ]);

} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}