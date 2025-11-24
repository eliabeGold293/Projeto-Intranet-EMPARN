<?php
require_once "../config/connection.php";

$id = isset($_POST["id"]) ? (int) $_POST["id"] : 0;

if ($id > 0) {
    try {
        // Buscar nome do usuário antes de excluir
        $stmtNome = $pdo->prepare("SELECT nome FROM usuario WHERE id = :id");
        $stmtNome->execute([":id" => $id]);
        $usuario = $stmtNome->fetch(PDO::FETCH_ASSOC);

        if (!$usuario) {
            echo "Usuário não encontrado.";
            exit;
        }
        // Excluir usuário
        $sql = "DELETE FROM usuario WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([":id" => $id]);

        // Registrar ação no log
        $descricao = "Usuário '{$usuario['nome']}' excluído";
        $stmtLog = $pdo->prepare("INSERT INTO log_acao (usuario_id, entidade, acao, descricao) 
                                  VALUES (:usuario_id, 'usuario', 'EXCLUIR', :descricao)");
        // Aqui você pode usar o ID do usuário logado na sessão, se houver.
        // Como exemplo, deixamos NULL.
        $stmtLog->execute([
            ':usuario_id' => null,
            ':descricao'  => $descricao
        ]);

        echo "Usuário deletado com sucesso!";
    } catch (PDOException $e) {
        echo "Erro ao tentar deletar: " . $e->getMessage();
    }
} else {
    echo "Informe um ID válido.";
}
?>
