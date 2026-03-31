<?php
header("Content-Type: application/json; charset=utf-8");

require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/../utils/log-action.php';
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

try {

    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        throw new Exception("Use POST.");
    }

    if (!isset($_POST["id"])) {
        throw new Exception("Nenhum ID recebido.");
    }

    $fileId = intval($_POST["id"]);

    if ($fileId <= 0) {
        throw new Exception("ID inválido.");
    }

    // ---------------------------
    // BUSCAR ARQUIVO
    // ---------------------------
    $stmt = $pdo->prepare("
        SELECT nome_original, caminho_armazenado 
        FROM documento_arquivo 
        WHERE id = :id
    ");
    $stmt->execute([":id" => $fileId]);
    $file = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$file) {
        throw new Exception("Arquivo não encontrado.");
    }

    $nomeArquivo = $file["nome_original"];
    $realPath = __DIR__ . "/../" . $file["caminho_armazenado"];

    $pdo->beginTransaction();

    // ---------------------------
    // REMOVE DO BANCO
    // ---------------------------
    $stmtDel = $pdo->prepare("DELETE FROM documento_arquivo WHERE id = :id");
    $stmtDel->execute([":id" => $fileId]);

    // ---------------------------
    // REMOVE ARQUIVO FÍSICO
    // ---------------------------
    if (file_exists($realPath)) {
        unlink($realPath);
    }

    // ---------------------------
    // BUSCAR USUÁRIO
    // ---------------------------
    $stmtUser = $pdo->prepare("SELECT nome FROM usuario WHERE id = :id");
    $stmtUser->execute([':id' => $_SESSION['usuario_id']]);

    $usuarioLog = $stmtUser->fetchColumn() ?? "Usuário desconhecido";

    // ---------------------------
    // LOG PADRÃO
    // ---------------------------
    try {
        registrarLog(
            $pdo,
            $_SESSION["usuario_id"] ?? null,
            "documento_arquivo",
            "DELETE",
            "Arquivo '{$nomeArquivo}' (ID {$fileId}) foi excluído na aba de Documentos Institucionais por '{$usuarioLog}'"
        );
    } catch (Exception $e) {
        error_log("Erro ao registrar log: " . $e->getMessage());
    }

    $pdo->commit();

    echo json_encode([
        "status" => "success",
        "message" => "Arquivo removido com sucesso."
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