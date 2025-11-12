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

// Busca todas as áreas da tabela area_atuacao
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
    <title>Cadastro de Usuário</title>
</head>
<style>
    /* Container do formulário */
    .form-container {
        margin-left: 320px; /* espaço para a sidebar */
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
    <?php include '../templates/gen_menu.php'; ?>

    <section class="form-container">
        <h2>Cadastro de Usuário</h2>
        <form action="../apis/criar_us.php" method="POST">
            <label>Nome:</label>
            <input type="text" name="nome" required>
            <br><br>

            <label>Email:</label>
            <input type="email" name="email" required>
            <br><br>

            <label>Senha:</label>
            <input type="password" name="senha" required>
            <br><br>

            <label>Classe:</label>
            <select name="classe_name" id="classe_name"> 
                <?php foreach ($classe_usuario as $class_us): ?>
                    <option value="<?=htmlspecialchars($class_us['nome'])?>">
                        <?=htmlspecialchars($class_us['nome'])?>
                    </option>
                <?php endforeach; ?>
            </select>
            <br><br>

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
