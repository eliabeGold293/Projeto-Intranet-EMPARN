<?php
require_once "../config/connection.php";

header("Content-Type: application/json");

try {
    $id = intval($_GET['id'] ?? 0);
    if ($id <= 0) {
        throw new Exception("ID inválido.");
    }

    // Buscar título do card antes de excluir
    $stmtTitulo = $pdo->prepare("SELECT titulo FROM dashboard WHERE id = ?");
    $stmtTitulo->execute([$id]);
    $card = $stmtTitulo->fetch(PDO::FETCH_ASSOC);

    if (!$card) {
        throw new Exception("Card não encontrado.");
    }

    // Excluir card
    $stmt = $pdo->prepare("DELETE FROM dashboard WHERE id = ?");
    $stmt->execute([$id]);

    // Registrar ação no log
    $descricao = "🗑️ Card '{$card['titulo']}' excluído";
    $stmtLog = $pdo->prepare("INSERT INTO log_acao (usuario_id, entidade, acao, descricao) 
                              VALUES (:usuario_id, 'dashboard', 'EXCLUIR', :descricao)");
    // Aqui você pode usar o ID do usuário logado na sessão, se houver. 
    // Como exemplo, deixamos NULL.
    $stmtLog->execute([
        ':usuario_id' => null,
        ':descricao'  => $descricao
    ]);

    echo json_encode([
        "status" => "success",
        "message" => "Card excluído com sucesso!"
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
?>
