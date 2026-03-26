<?php
require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/../utils/log-action.php';

session_start();

$id = isset($_POST["id"]) ? (int) $_POST["id"] : 0;

// Usuário logado
$usuarioLogadoId = $_SESSION['usuario_id'] ?? null;

if ($id > 0) {
    try {
        // Buscar nome antes de excluir
        $stmtNome = $pdo->prepare("SELECT nome FROM area_atuacao WHERE id = :id");
        $stmtNome->execute([":id" => $id]);
        $area = $stmtNome->fetch(PDO::FETCH_ASSOC);

        if (!$area) {
            echo "Área não encontrada.";
            exit;
        }

        // Excluir
        $sql = "DELETE FROM area_atuacao WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([":id" => $id]);

        // ============================
        // LOG PADRONIZADO
        // ============================
        $descricao = "Área '{$area['nome']}' excluída";

        registrarLog(
            $pdo,
            $usuarioLogadoId,
            "area_atuacao",
            "DELETE",
            $descricao
        );

        echo "Área de Atuação deletada com sucesso!";

    } catch (PDOException $e) {

        if ($e->getCode() === '23503') {
            echo "Não é possível excluir esta área porque existem usuários vinculados a ela.";
        } else {
            error_log("Erro ao excluir área: " . $e->getMessage());
            echo "Erro ao excluir área.";
        }

    } catch (Exception $e) {
        error_log("Erro geral: " . $e->getMessage());
        echo "Erro inesperado.";
    }

} else {
    echo "Informe um ID válido.";
}
?>