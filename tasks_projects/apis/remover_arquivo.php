<?php
header("Content-Type: application/json; charset=utf-8");
require_once "../config/connection.php";

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

    // Busca o arquivo
    $stmt = $pdo->prepare("SELECT caminho_armazenado FROM documento_arquivo WHERE id = :id");
    $stmt->execute([":id" => $fileId]);
    $file = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$file) {
        throw new Exception("Arquivo não encontrado.");
    }

    $realPath = __DIR__ . "/../" . $file["caminho_armazenado"];

    $pdo->beginTransaction();

    // Remove do banco
    $stmtDel = $pdo->prepare("DELETE FROM documento_arquivo WHERE id = :id");
    $stmtDel->execute([":id" => $fileId]);

    // Remove arquivo físico
    if (file_exists($realPath)) {
        unlink($realPath);
    }

    /**
     * ============================
     *   LOG DE AÇÃO ADICIONADO
     * ============================
     */
    $log = $pdo->prepare("
        INSERT INTO log_acao (usuario_id, entidade, acao, descricao)
        VALUES (:usuario_id, :entidade, :acao, :descricao)
    ");

    $log->execute([
        ":usuario_id" => $_SESSION["usuario_id"] ?? null,  // se não tiver sessão, grava NULL
        ":entidade"   => "documento_arquivo",
        ":acao"       => "EXCLUIR",
        ":descricao"  => "Arquivo ID {$fileId} removido."
    ]);
    /**
     * ============================
     */

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