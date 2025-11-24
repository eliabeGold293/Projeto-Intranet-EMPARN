<?php 
require_once "../config/connection.php";

$id   = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$nome = $_POST['nome'] ?? null;

if ($id > 0) {
    try {
        if ($nome) {
            // Buscar nome antigo da área antes da atualização
            $stmtAntigo = $pdo->prepare("SELECT nome FROM area_atuacao WHERE id = :id");
            $stmtAntigo->execute([':id' => $id]);
            $areaAntiga = $stmtAntigo->fetch(PDO::FETCH_ASSOC);

            if (!$areaAntiga) {
                echo "Área não encontrada.";
                exit;
            }

            // Atualizar área
            $sql = "UPDATE area_atuacao SET nome = :nome WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nome' => $nome,
                ':id'   => $id
            ]);

            // Registrar ação no log
            $descricao = "Área de Atuação '{$areaAntiga['nome']}' atualizada para '{$nome}'";
            $stmtLog = $pdo->prepare("INSERT INTO log_acao (usuario_id, entidade, acao, descricao) 
                                      VALUES (:usuario_id, 'area_atuacao', 'ATUALIZAR', :descricao)");
            // Aqui você pode usar o ID do usuário logado na sessão, se houver.
            // Como exemplo, deixamos NULL.
            $stmtLog->execute([
                ':usuario_id' => null,
                ':descricao'  => $descricao
            ]);

            echo "Alterações realizadas com sucesso!";
        } else {
            echo "Nenhum campo foi informado para atualização.";
        }

    } catch (PDOException $e) {
        if ($e->getCode() === '23503') {
            // Esse código só deve aparecer em DELETE, não em UPDATE de nome
            echo "Não é possível excluir esta área porque existem usuários vinculados a ela.";
        } else {
            echo "Erro ao salvar alterações: " . $e->getMessage();
        }
    }
} else {
    echo "Informe um ID válido.";
}
?>
