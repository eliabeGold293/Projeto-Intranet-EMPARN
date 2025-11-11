<?php

require_once "../config/connection.php"; // já contém $pdo

function gerarSelectClasses($name = "classe_name") {
    global $pdo;

    try {
        // Busca todas as classes da tabela classe_usuario
        $stmt = $pdo->prepare("SELECT id, nome, grau_acesso FROM classe_usuario ORDER BY nome ASC");
        $stmt->execute();
        $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Monta o select
        echo "<select name='{$name}' id='{$name}'>";
        foreach ($classes as $classe) {
            $id   = htmlspecialchars($classe["id"]);
            $nome = htmlspecialchars($classe["nome"]);
            $grau = htmlspecialchars($classe["grau_acesso"]);

            // Exibe o nome e opcionalmente o grau de acesso
            echo "<option value='{$id}'>{$nome} (Acesso: {$grau})</option>";
        }
        echo "</select>";

    } catch (PDOException $e) {
        echo "Erro ao carregar classes: " . $e->getMessage();
    }
}
