<?php
header("Content-Type: application/json; charset=UTF-8");
require_once "../config/connection.php";
session_start(); // para registrar no log quem fez a ação

try {

    $id = $_POST['id'] ?? $_GET['id'] ?? null;

    if (!$id) {
        echo json_encode([
            "status" => "error",
            "message" => "ID do tópico não informado."
        ]);
        exit;
    }

    // 1 — Obter nome do tópico (será usado no log)
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

    // 2 — Buscar arquivos associados ao tópico
    $queryFiles = $pdo->prepare("
        SELECT caminho_armazenado 
        FROM documento_arquivo
        WHERE topico_id = :id
    ");
    $queryFiles->execute([":id" => $id]);
    $arquivos = $queryFiles->fetchAll(PDO::FETCH_ASSOC);

    // 3 — Apagar arquivos físicos
    if (!empty($arquivos)) {

        foreach ($arquivos as $arq) {
            $relative = $arq['caminho_armazenado'];
            if (!$relative) continue;

            // Caminho absoluto
            $absolute = realpath(__DIR__ . "/../" . $relative);

            if ($absolute && file_exists($absolute) && is_file($absolute)) {
                unlink($absolute);
            }
        }

        // 4 — Remover a pasta principal do tópico
        $topicFolder = dirname($arquivos[0]['caminho_armazenado']);
        $absoluteFolder = realpath(__DIR__ . "/../" . $topicFolder);

        if ($absoluteFolder && is_dir($absoluteFolder)) {

            foreach (glob($absoluteFolder . "/*") as $file) {
                if (is_file($file)) unlink($file);
            }

            @rmdir($absoluteFolder);
        }
    }

    // 5 — Excluir arquivos do banco
    $delFiles = $pdo->prepare("DELETE FROM documento_arquivo WHERE topico_id = :id");
    $delFiles->execute([":id" => $id]);

    // 6 — Excluir tópico
    $delete = $pdo->prepare("DELETE FROM documento_topico WHERE id = :id");
    $delete->execute([":id" => $id]);

    // 7 — Registrar log
    $stmtLog = $pdo->prepare("
        INSERT INTO log_acao (usuario_id, entidade, acao, descricao)
        VALUES (:usuario_id, 'documento_topico', 'EXCLUIR', :descricao)
    ");

    $descricao = "Tópico '{$topico['nome']}' excluído.";

    $stmtLog->execute([
        ":usuario_id" => $_SESSION['usuario_id'] ?? null,
        ":descricao"  => $descricao
    ]);

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
