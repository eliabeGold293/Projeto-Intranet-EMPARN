<?php
require_once "../config/connection.php";

$id = isset($_POST["id"]) ? (int) $_POST["id"] : 0;

if ($id > 0) {
    try {
        $sql = "DELETE FROM usuario WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([":id" => $id]);

        echo "Usuário deletado com sucesso!";
    } catch (PDOException $e) {
        echo "Erro ao tentar deletar: " . $e->getMessage();
    }
} else {
    echo "Informe um ID válido.";
}
