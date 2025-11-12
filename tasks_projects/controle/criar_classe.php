<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Criar Classe</title>
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

    .form-container input {
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
        <h2>Criar Classe</h2>
        <form action="../apis/criar_classe_us.php" method="POST">
            <label>Nome da Classe:</label>
            <input type="text" name="nome" required>

            <label>Grau de Acesso (1 a 4):</label>
            <input type="number" name="grau_acesso" min="1" max="4" required>

            <button type="submit">Salvar</button>
        </form>
    </section>
</body>
</html>
