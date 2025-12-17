<?php
require_once __DIR__ . '/../config/connection.php';

$id = isset($_POST["id"]) ? (int) $_POST["id"] : 0;

if ($id > 0) {
    try {
        // Buscar nome da classe antes de excluir
        $stmtNome = $pdo->prepare("SELECT nome FROM classe_usuario WHERE id = :id");
        $stmtNome->execute([":id" => $id]);
        $classe = $stmtNome->fetch(PDO::FETCH_ASSOC);

        if (!$classe) {
            echo "Classe não encontrada.";
            exit;
        }

        // Excluir classe
        $sql = "DELETE FROM classe_usuario WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([":id" => $id]);

        // Registrar ação no log
        $descricao = "Classe de Usuário '{$classe['nome']}' excluída";
        $stmtLog = $pdo->prepare("INSERT INTO log_acao (usuario_id, entidade, acao, descricao) 
                                  VALUES (:usuario_id, 'classe_usuario', 'EXCLUIR', :descricao)");
        // Aqui você pode usar o ID do usuário logado na sessão, se houver. 
        // Como exemplo, deixamos NULL.
        $stmtLog->execute([
            ':usuario_id' => null,
            ':descricao'  => $descricao
        ]);

        echo "Classe deletada com sucesso!";
    } catch (PDOException $e) {
        if ($e->getCode() === '23503') {
            // Mensagem amigável para o usuário
            echo "Não é possível excluir esta classe porque existem usuários vinculados a ela.";
        } else {
            echo "Erro ao excluir classe: " . $e->getMessage();
        }
    }
} else {
    echo "Informe um ID válido.";
}
?>
