<?php
header("Content-Type: application/json; charset=UTF-8");
require_once "../config/connection.php";

try {

    $id = $_POST['id'] ?? $_GET['id'] ?? null;

    if (!$id) {
        echo json_encode([
            "status" => "error",
            "message" => "ID do tópico não informado."
        ]);
        exit;
    }

    // 1 — Verificar se o tópico existe ANTES de tudo
    $check = $pdo->prepare("SELECT id FROM documento_topico WHERE id = :id");
    $check->execute([":id" => $id]);

    if ($check->rowCount() === 0) {
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

    // 3 — Apagar arquivos físicos (se houver)
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
        $topicFolder = dirname($arquivos[0]['caminho_armazenado']); // Já validado acima

        $absoluteFolder = realpath(__DIR__ . "/../" . $topicFolder);

        if ($absoluteFolder && is_dir($absoluteFolder)) {

            // Remover arquivos restantes
            foreach (glob($absoluteFolder . "/*") as $file) {
                if (is_file($file)) unlink($file);
            }

            // Remover pasta
            @rmdir($absoluteFolder);
        }

    }
    // Caso NÃO haja arquivos → simplesmente segue adiante

    // 5 — Excluir tópico do banco
    $delete = $pdo->prepare("DELETE FROM documento_topico WHERE id = :id");
    $delete->execute([":id" => $id]);

    echo json_encode([
        "status" => "success",
        "message" => "Tópico removido com sucesso. (Arquivos e pastas inexistentes foram ignorados)"
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