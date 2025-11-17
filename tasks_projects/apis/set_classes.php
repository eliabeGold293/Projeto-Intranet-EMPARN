<?php 
require_once "../config/connection.php";

$id          = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$nome        = $_POST['nome'] ?? null;
$grau_acesso = isset($_POST['grau_acesso']) ? (int) $_POST['grau_acesso'] : 0;

if ($id > 0) {
    try {
        $campos = [];
        $params = [':id' => $id];

        if ($nome) {
            $campos[] = "nome = :nome";
            $params[':nome'] = $nome;
        }
        if ($grau_acesso) { // corrigido: antes estava $garu_acesso
            $campos[] = "grau_acesso = :grau_acesso";
            $params[':grau_acesso'] = $grau_acesso;
        }

        if (!empty($campos)) {
            $sql = "UPDATE classe_usuario SET " . implode(", ", $campos) . " WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

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
