<?php
require_once "../config/connection.php";

// Busca todas as classes da tabela classe_usuario
try {
    $stmt = $pdo->prepare("SELECT id, nome, grau_acesso FROM classe_usuario ORDER BY nome ASC");
    $stmt->execute();
    $classe_usuario = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro ao carregar classes: " . $e->getMessage());
}

try {
    $stmt = $pdo->prepare("SELECT id, nome FROM area_atuacao ORDER BY nome ASC");
    $stmt->execute();
    $areas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro ao carregar áreas: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Usuário</title>
    <link rel="stylesheet" href="style.css"> <!-- CSS separado -->
</head>
<style>
    /* Container do formulário */
    .form-container {
        margin-left: 320px; /* espaço para a sidebar */
        margin-top: 40px;
        padding: 20px;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        max-width: 600px;
    }

    .form-container h2 {
        margin-top: 0;
        color: #333;
        font-family: Arial, sans-serif;
    }

    .form-container input,
    .form-container select {
        width: 100%;
        padding: 8px;
        margin-top: 5px;
        margin-bottom: 15px;
        border: 1px solid #ccc;
        border-radius: 4px;
    }

    .form-container button {
        background: #4a90e2;
        color: #fff;
        border: none;
        padding: 10px 15px;
        border-radius: 4px;
        cursor: pointer;
        transition: background 0.3s ease;
    }

    .form-container button:hover {
        background: #357ab8;
    }
</style>
<body>
    <!-- Navegação reutilizável -->
    <?php include '../templates/gen_menu.php'; ?>

    <!-- Encapsulamento do conteúdo -->
    <section class="form-container">
        <h2>Alterar Informações do Usuário</h2>
        <form action="../apis/set_us.php" method="POST">
            <!-- Campo ID para identificar o usuário -->
            <label>ID do Usuário:</label>
            <input type="number" name="id" required>

            <label>Nome:</label>
            <input type="text" name="nome">

            <label>Email:</label>
            <input type="email" name="email">

            <label>Senha:</label>
            <input type="password" name="senha">

            <label>Classe:</label>
            <select name="classe_name" id="classe_name"> 
                <option value="">-- Selecione --</option>
                <?php foreach ($classe_usuario as $class_us): ?>
                    <option value="<?=htmlspecialchars($class_us['nome'])?>">
                        <?=htmlspecialchars($class_us['nome'])?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label>Área:</label>
            <select name="area_name" id="area_name">
                <?php foreach ($areas as $area): ?>
                    <option value="<?=htmlspecialchars($area['nome'])?>">
                        <?=htmlspecialchars($area['nome'])?>
                    </option>
                <?php endforeach; ?>
            </select>
            <br><br>

            <button type="submit">Salvar</button>
        </form>
    </section>
</body>
</html>
