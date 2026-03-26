<?php 
require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/../utils/log-action.php';

session_start();

$id          = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$nome        = $_POST['nome'] ?? null;
$grau_acesso = isset($_POST['grau_acesso']) ? (int) $_POST['grau_acesso'] : 0;

// Usuário logado (se existir)
$usuarioLogadoId = $_SESSION['usuario_id'] ?? null;

if ($id > 0) {
    try {
        // Buscar dados antigos
        $stmtAntigo = $pdo->prepare("SELECT nome, grau_acesso FROM classe_usuario WHERE id = :id");
        $stmtAntigo->execute([':id' => $id]);
        $classeAntiga = $stmtAntigo->fetch(PDO::FETCH_ASSOC);

        if (!$classeAntiga) {
            echo "Classe não encontrada.";
            exit;
        }

        $campos = [];
        $params = [':id' => $id];

        if ($nome) {
            $campos[] = "nome = :nome";
            $params[':nome'] = $nome;
        }

        if ($grau_acesso) {
            $campos[] = "grau_acesso = :grau_acesso";
            $params[':grau_acesso'] = $grau_acesso;
        }

        if (!empty($campos)) {

            $sql = "UPDATE classe_usuario SET " . implode(", ", $campos) . " WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            // ============================
            // LOG PADRONIZADO
            // ============================
            $descricao = "Classe '{$classeAntiga['nome']}' (grau {$classeAntiga['grau_acesso']}) atualizada";

            if ($nome) {
                $descricao .= " → nome: '{$nome}'";
            }

            if ($grau_acesso) {
                $descricao .= " → grau: {$grau_acesso}";
            }

            registrarLog(
                $pdo,
                $usuarioLogadoId,
                "classe_usuario",
                "UPDATE",
                $descricao
            );

            echo "Alterações realizadas com sucesso!";
        } else {
            echo "Nenhum campo foi informado para atualização.";
        }

    } catch (Exception $e) {
        error_log("Erro na API atualizar classe: " . $e->getMessage());
        echo "Erro ao salvar alterações.";
    }

} else {
    echo "Informe um ID válido.";
}
?>