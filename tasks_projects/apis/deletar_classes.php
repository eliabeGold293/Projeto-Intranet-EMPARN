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
        echo "Erro ao tentar deletar classe: " . $e->getMessage();
    }
} else {
    echo "Informe um ID válido.";
}

?>