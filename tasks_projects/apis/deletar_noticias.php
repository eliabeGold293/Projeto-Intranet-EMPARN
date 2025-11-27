<?php
require_once "../config/connection.php"; // $pdo está disponível

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

    // IMPORTANTE: deletar tópicos manualmente *caso não exista ON DELETE CASCADE*
    // Se já existir CASCADE, não é necessário, mas não causa erro.
    $delTopicos = $pdo->prepare("DELETE FROM noticia_topicos WHERE noticia_id = :id");
    $delTopicos->execute([':id' => $idNoticia]);

    // Deletar notícia
    $deleteStmt = $pdo->prepare("DELETE FROM noticias WHERE id = :id");
    $deleteStmt->execute([':id' => $idNoticia]);

    // Registrar no log
    $stmtLog = $pdo->prepare("
        INSERT INTO log_acao (usuario_id, entidade, acao, descricao) 
        VALUES (:usuario_id, 'noticias', 'DELETAR', :descricao)
    ");

    $stmtLog->execute([
        ':usuario_id' => null, // coloque o ID do usuário logado quando implementar login
        ':descricao'  => "Notícia '{$tituloNoticia}' deletada"
    ]);

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
