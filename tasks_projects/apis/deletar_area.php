<?php
require_once __DIR__ . '/../config/connection.php';

$id = isset($_POST["id"]) ? (int) $_POST["id"] : 0;

if ($id > 0) {
    try {
        // Antes de excluir, vamos buscar o nome da área para registrar no log
        $stmtNome = $pdo->prepare("SELECT nome FROM area_atuacao WHERE id = :id");
        $stmtNome->execute([":id" => $id]);
        $area = $stmtNome->fetch(PDO::FETCH_ASSOC);

        if (!$area) {
            echo "Área não encontrada.";
            exit;
        }

        // Excluir área
        $sql = "DELETE FROM area_atuacao WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([":id" => $id]);

        // Registrar ação no log
        $descricao = "🗑️ Área de Atuação '{$area['nome']}' excluída";
        $stmtLog = $pdo->prepare("INSERT INTO log_acao (usuario_id, entidade, acao, descricao) 
                                  VALUES (:usuario_id, 'area_atuacao', 'EXCLUIR', :descricao)");
        // Aqui você pode usar o ID do usuário logado na sessão, se houver. 
        // Como exemplo, deixamos NULL.
        $stmtLog->execute([
            ':usuario_id' => null,
            ':descricao'  => $descricao
        ]);

        echo "Área de Atuação deletada com sucesso!";
    } catch (PDOException $e) {
        if ($e->getCode() === '23503') {
            // Mensagem amigável para o usuário
            echo "Não é possível excluir esta área porque existem usuários vinculados a ela.";
        } else {
            echo "Erro ao excluir área: " . $e->getMessage();
        }
    }
} else {
    echo "Informe um ID válido.";
}
?>
