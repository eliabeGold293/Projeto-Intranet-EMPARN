<?php 
require_once "../config/connection.php";

$id          = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$nome        = $_POST['nome'] ?? null;
$grau_acesso = isset($_POST['grau_acesso']) ? (int) $_POST['grau_acesso'] : 0;

if ($id > 0) {
    try {
        // Buscar dados antigos da classe antes da atualização
        $stmtAntigo = $pdo->prepare("SELECT nome, grau_acesso FROM classe_usuario WHERE id = :id");
        $stmtAntigo->execute([':id' => $id]);
        $classeAntiga = $stmtAntigo->fetch(PDO::FETCH_ASSOC);

        if (!$classeAntiga) {
            echo "❌ Classe não encontrada.";
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

            // Registrar ação no log
            $descricao = "✏️ Classe de Usuário '{$classeAntiga['nome']}' (grau {$classeAntiga['grau_acesso']}) atualizada";
            if ($nome) {
                $descricao .= " → novo nome: '{$nome}'";
            }
            if ($grau_acesso) {
                $descricao .= " → novo grau: {$grau_acesso}";
            }

            $stmtLog = $pdo->prepare("INSERT INTO log_acao (usuario_id, entidade, acao, descricao) 
                                      VALUES (:usuario_id, 'classe_usuario', 'ATUALIZAR', :descricao)");
            // Aqui você pode usar o ID do usuário logado na sessão, se houver.
            // Como exemplo, deixamos NULL.
            $stmtLog->execute([
                ':usuario_id' => null,
                ':descricao'  => $descricao
            ]);

            echo "✅ Alterações realizadas com sucesso!";
        } else {
            echo "⚠️ Nenhum campo foi informado para atualização.";
        }

    } catch (PDOException $e) {
        echo "❌ Erro ao salvar alterações: " . $e->getMessage();
    }
} else {
    echo "⚠️ Informe um ID válido.";
}
?>
