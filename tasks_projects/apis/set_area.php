<?php 
require_once "../config/connection.php";

$id   = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$nome = $_POST['nome'] ?? null;

if ($id > 0) {
    try {
        if ($nome) {
            $sql = "UPDATE area_atuacao SET nome = :nome WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nome' => $nome,
                ':id'   => $id
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
