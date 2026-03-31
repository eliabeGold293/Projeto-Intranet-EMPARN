<?php
header("Content-Type: application/json");

require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/../utils/log-action.php';
session_start();

try {

    if (!isset($_POST['id']) || !isset($_FILES['arquivo'])) {
        throw new Exception("Dados incompletos");
    }

    $id = intval($_POST['id']);
    $file = $_FILES['arquivo'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("Erro no upload");
    }

    // ---------------------------
    // BUSCAR ARQUIVO ANTIGO
    // ---------------------------
    $stmt = $pdo->prepare("
        SELECT nome_original, caminho_armazenado 
        FROM documento_arquivo 
        WHERE id = ?
    ");
    $stmt->execute([$id]);
    $old = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$old) {
        throw new Exception("Arquivo não encontrado");
    }

    $nomeAntigo = $old['nome_original'];
    $oldPath = __DIR__ . "/../" . $old['caminho_armazenado'];

    // ---------------------------
    // SALVAR NOVO ARQUIVO
    // ---------------------------
    $orig = basename($file['name']);
    $tmp  = $file['tmp_name'];
    $type = $file['type'];
    $size = $file['size'];

    $folder = __DIR__ . "/../uploads/doc/";

    if (!is_dir($folder)) {
        mkdir($folder, 0777, true);
    }

    $novoNome = uniqid("f_") . "_" . $orig;
    $destino = $folder . $novoNome;

    if (!move_uploaded_file($tmp, $destino)) {
        throw new Exception("Erro ao salvar arquivo");
    }

    $caminhoRelativo = "uploads/doc/" . $novoNome;

    // ---------------------------
    // ATUALIZAR BANCO
    // ---------------------------
    $stmt = $pdo->prepare("
        UPDATE documento_arquivo
        SET nome_original = ?, 
            caminho_armazenado = ?, 
            tipo = ?, 
            tamanho = ?
        WHERE id = ?
    ");

    $stmt->execute([
        $orig,
        $caminhoRelativo,
        $type,
        $size,
        $id
    ]);

    // ---------------------------
    // DELETAR ANTIGO
    // ---------------------------
    if (file_exists($oldPath)) {
        unlink($oldPath);
    }

    // ---------------------------
    // BUSCAR USUÁRIO
    // ---------------------------
    $stmtUser = $pdo->prepare("SELECT nome FROM usuario WHERE id = :id");
    $stmtUser->execute([':id' => $_SESSION['usuario_id']]);

    $usuarioLog = $stmtUser->fetchColumn() ?? "Usuário desconhecido";

    // ---------------------------
    // LOG
    // ---------------------------
    try {
        registrarLog(
            $pdo,
            $_SESSION['usuario_id'] ?? null,
            'documento_arquivo',
            'UPDATE',
            "Arquivo '{$nomeAntigo}' foi substituído por '{$orig}' por '{$usuarioLog}' (ID {$id}) em Documentos institucionais"
        );
    } catch (Exception $e) {
        error_log("Erro ao registrar log: " . $e->getMessage());
    }

    echo json_encode(["status" => "success"]);

} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}