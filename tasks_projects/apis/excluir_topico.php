<?php
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/../utils/log-action.php';
session_start();

try {

    $id = $_POST['id'] ?? $_GET['id'] ?? null;

    if (!$id) {
        echo json_encode([
            "status" => "error",
            "message" => "ID do tópico não informado."
        ]);
        exit;
    }

    // ================================
    // 1 — OBTER NOME (PARA LOG)
    // ================================
    $stmtNome = $pdo->prepare("SELECT nome FROM documento_topico WHERE id = :id");
    $stmtNome->execute([":id" => $id]);

    $topico = $stmtNome->fetch(PDO::FETCH_ASSOC);

    if (!$topico) {
        echo json_encode([
            "status" => "error",
            "message" => "Tópico não encontrado."
        ]);
        exit;
    }

    // ================================
    // 2 — BUSCAR ARQUIVOS
    // ================================
    $queryFiles = $pdo->prepare("
        SELECT caminho_armazenado 
        FROM documento_arquivo
        WHERE topico_id = :id
    ");
    $queryFiles->execute([":id" => $id]);
    $arquivos = $queryFiles->fetchAll(PDO::FETCH_ASSOC);

    // ================================
    // 3 — REMOVER ARQUIVOS FÍSICOS
    // ================================
    if (!empty($arquivos)) {

        foreach ($arquivos as $arq) {
            $relative = $arq['caminho_armazenado'];
            if (!$relative) continue;

            $absolute = realpath(__DIR__ . "/../" . $relative);

            if ($absolute && file_exists($absolute) && is_file($absolute)) {
                unlink($absolute);
            }
        }

        // ================================
        // 4 — REMOVER PASTA
        // ================================
        $topicFolder = dirname($arquivos[0]['caminho_armazenado']);
        $absoluteFolder = realpath(__DIR__ . "/../" . $topicFolder);

        if ($absoluteFolder && is_dir($absoluteFolder)) {

            foreach (glob($absoluteFolder . "/*") as $file) {
                if (is_file($file)) unlink($file);
            }

            @rmdir($absoluteFolder);
        }
    }

    // ================================
    // 5 — REMOVER DO BANCO
    // ================================
    $delFiles = $pdo->prepare("DELETE FROM documento_arquivo WHERE topico_id = :id");
    $delFiles->execute([":id" => $id]);

    $delete = $pdo->prepare("DELETE FROM documento_topico WHERE id = :id");
    $delete->execute([":id" => $id]);

    // ================================
    // 6 — LOG (PADRÃO NOVO)
    // ================================
    try {
        registrarLog(
            $pdo,
            $_SESSION['usuario_id'] ?? null,
            'documento_topico',
            'DELETE',
            "Tópico '{$topico['nome']}' (ID {$id}) excluído"
        );
    } catch (Exception $e) {
        error_log("Erro ao registrar log: " . $e->getMessage());
    }

    // ================================
    // RESPOSTA
    // ================================
    echo json_encode([
        "status" => "success",
        "message" => "Tópico removido com sucesso."
    ]);
    exit;

} catch (PDOException $e) {

    echo json_encode([
        "status" => "error",
        "message" => "Erro no banco de dados: " . $e->getMessage()
    ]);
    exit;

} catch (Exception $e) {

    echo json_encode([
        "status" => "error",
        "message" => "Erro inesperado: " . $e->getMessage()
    ]);
    exit;
}
?>