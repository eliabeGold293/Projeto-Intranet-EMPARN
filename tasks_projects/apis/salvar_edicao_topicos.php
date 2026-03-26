<?php
header("Content-Type: application/json; charset=utf-8");
require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/../utils/log-action.php';
session_start();

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

    // CONTADORES 
    $arquivosAdicionados = 0;
    $arquivosRemovidos = 0;

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
    // 2) NOVOS ARQUIVOS
    // -------------------------------------
    if (isset($_FILES['novos_arquivos'])) {

        $arquivos = $_FILES['novos_arquivos'];

        for ($i = 0; $i < count($arquivos['name']); $i++) {

            if ($arquivos['error'][$i] !== UPLOAD_ERR_OK) continue;

            $orig  = basename($arquivos['name'][$i]);
            $tmp   = $arquivos['tmp_name'][$i];
            $type  = $arquivos['type'][$i];
            $size  = $arquivos['size'][$i];

            if (!is_uploaded_file($tmp)) {
                throw new Exception("Falha ao validar arquivo.");
            }

            $unique = uniqid('f_', true) . "_" . $orig;
            $dest = $topicFolder . $unique;

            if (!move_uploaded_file($tmp, $dest)) {
                throw new Exception("Erro ao salvar arquivo.");
            }

            $relative = "uploads/doc/topic_{$id}/{$unique}";

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

            $arquivosAdicionados++; //
        }
    }

    // -------------------------------------
    // 3) REMOVER ARQUIVOS (SE ENVIADO)
    // -------------------------------------
    if (!empty($_POST['arquivos_removidos'])) {

        $idsRemover = json_decode($_POST['arquivos_removidos'], true);

        if (is_array($idsRemover) && count($idsRemover) > 0) {

            // buscar caminhos
            $in = implode(",", array_fill(0, count($idsRemover), "?"));
            $stmt = $pdo->prepare("SELECT caminho_armazenado FROM documento_arquivo WHERE id IN ($in)");
            $stmt->execute($idsRemover);
            $arquivos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($arquivos as $arq) {
                $path = realpath(__DIR__ . "/../" . $arq['caminho_armazenado']);
                if ($path && file_exists($path)) {
                    unlink($path);
                }
                $arquivosRemovidos++; //
            }

            // remover do banco
            $stmt = $pdo->prepare("DELETE FROM documento_arquivo WHERE id IN ($in)");
            $stmt->execute($idsRemover);
        }
    }

    $pdo->commit();

    // -------------------------------------
    // 4) LOG INTELIGENTE
    // -------------------------------------
    $detalhes = [];

    if ($arquivosAdicionados > 0) {
        $detalhes[] = "{$arquivosAdicionados} arquivo(s) adicionados";
    }

    if ($arquivosRemovidos > 0) {
        $detalhes[] = "{$arquivosRemovidos} arquivo(s) removidos";
    }

    $descricaoFinal = "Tópico '{$nome}' (ID {$id}) atualizado";

    if (!empty($detalhes)) {
        $descricaoFinal .= " (" . implode(", ", $detalhes) . ")";
    }

    registrarLog(
        $pdo,
        $_SESSION['usuario_id'] ?? null,
        'documento_topico',
        'UPDATE',
        $descricaoFinal
    );

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