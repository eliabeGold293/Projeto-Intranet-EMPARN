<?php
require_once "../config/connection.php";

$id = isset($_POST["id"]) ? (int) $_POST["id"] : 0;

if ($id > 0) {
    try {
        $sql = "DELETE FROM classe_usuario WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([":id" => $id]);

        echo "Classe deletada com sucesso!";
    } catch (PDOException $e) {
        if ($e->getCode() === '23503') {
            // Mensagem amigável para o usuário
            echo "❌ Não é possível excluir esta área porque existem usuários vinculados a ela.";
        } else {
            echo "❌ Erro ao excluir área: " . $e->getMessage();
        }
    }
} else {
    echo "Informe um ID válido.";
}

?>