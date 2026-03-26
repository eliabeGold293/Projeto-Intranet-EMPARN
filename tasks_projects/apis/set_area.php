<?php 
require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/../utils/log-action.php';

session_start();

$id   = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$nome = $_POST['nome'] ?? null;

// Usuário logado
$usuarioLogadoId = $_SESSION['usuario_id'] ?? null;

if ($id > 0) {
    try {

        if ($nome) {

            // Buscar dados antigos
            $stmtAntigo = $pdo->prepare("SELECT nome FROM area_atuacao WHERE id = :id");
            $stmtAntigo->execute([':id' => $id]);
            $areaAntiga = $stmtAntigo->fetch(PDO::FETCH_ASSOC);

            if (!$areaAntiga) {
                echo "Área não encontrada.";
                exit;
            }

            // Atualizar
            $sql = "UPDATE area_atuacao SET nome = :nome WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nome' => $nome,
                ':id'   => $id
            ]);

            // ============================
            // LOG PADRONIZADO
            // ============================
            $descricao = "Área '{$areaAntiga['nome']}' atualizada → '{$nome}'";

            registrarLog(
                $pdo,
                $usuarioLogadoId,
                "area_atuacao",
                "UPDATE",
                $descricao
            );

            echo "Alterações realizadas com sucesso!";

        } else {
            echo "Nenhum campo foi informado para atualização.";
        }

    } catch (Exception $e) {
        error_log("Erro ao atualizar área: " . $e->getMessage());
        echo "Erro ao salvar alterações.";
    }

} else {
    echo "Informe um ID válido.";
}
?>