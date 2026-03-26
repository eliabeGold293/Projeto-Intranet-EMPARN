<?php
require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/../utils/log-action.php';
session_start();

header('Content-Type: application/json');

try {

    // ID pode vir de POST ou GET
    $idNoticia = $_POST['id'] ?? $_GET['id'] ?? null;

    if (!$idNoticia) {
        throw new Exception("ID da notícia não informado.");
    }

    // Verificar se a notícia existe
    $checkStmt = $pdo->prepare("SELECT titulo FROM noticias WHERE id = :id");
    $checkStmt->execute([':id' => $idNoticia]);
    $noticia = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if (!$noticia) {
        throw new Exception("Notícia não encontrada.");
    }

    $tituloNoticia = $noticia['titulo'];

    // ================================
    // REMOVER TÓPICOS
    // ================================
    $delTopicos = $pdo->prepare("DELETE FROM noticia_topicos WHERE noticia_id = :id");
    $delTopicos->execute([':id' => $idNoticia]);

    // ================================
    // DELETAR NOTÍCIA
    // ================================
    $deleteStmt = $pdo->prepare("DELETE FROM noticias WHERE id = :id");
    $deleteStmt->execute([':id' => $idNoticia]);

    // ================================
    // LOG (PADRÃO NOVO)
    // ================================
    try {
        registrarLog(
            $pdo,
            $_SESSION['usuario_id'] ?? null,
            'noticia',
            'DELETE',
            "Notícia '{$tituloNoticia}' (ID {$idNoticia}) excluída"
        );
    } catch (Exception $e) {
        error_log("Erro ao registrar log: " . $e->getMessage());
    }

    // ================================
    // RESPOSTA
    // ================================
    echo json_encode([
        "status" => "success",
        "message" => "Notícia deletada com sucesso."
    ]);
    exit;

} catch (Exception $e) {

    http_response_code(500);

    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
    exit;
}
?>