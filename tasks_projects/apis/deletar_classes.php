<?php
require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/../utils/log-action.php';

session_start();

$id = isset($_POST["id"]) ? (int) $_POST["id"] : 0;

// Usuário logado (se existir)
$usuarioLogadoId = $_SESSION['usuario_id'] ?? null;

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

        // ============================
        // LOG PADRONIZADO
        // ============================
        $descricao = "Classe '{$classe['nome']}' excluída";

        registrarLog(
            $pdo,
            $usuarioLogadoId,
            "classe_usuario",
            "DELETE",
            $descricao
        );

        echo "Classe deletada com sucesso!";

    } catch (PDOException $e) {

        if ($e->getCode() === '23503') {
            // Violação de chave estrangeira
            echo "Não é possível excluir esta classe porque existem usuários vinculados a ela.";
        } else {
            error_log("Erro ao excluir classe: " . $e->getMessage());
            echo "Erro ao excluir classe.";
        }

    } catch (Exception $e) {
        error_log("Erro geral: " . $e->getMessage());
        echo "Erro inesperado.";
    }

} else {
    echo "Informe um ID válido.";
}
?>