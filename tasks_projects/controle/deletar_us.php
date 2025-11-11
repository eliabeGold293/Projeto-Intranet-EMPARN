<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Deletar Usuário</title>
    <link rel="stylesheet" href="style.css"> <!-- CSS separado -->
</head>
<style>
    /* Container do formulário */
    .form-container {
        margin-left: 320px; /* espaço para a sidebar */
        margin-top: 40px;   /* afastamento do topo */
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
        background: #e74c3c;
        color: #fff;
        border: none;
        padding: 10px 15px;
        border-radius: 4px;
        cursor: pointer;
        transition: background 0.3s ease;
    }

    .form-container button:hover {
        background: #c0392b;
    }
</style>
<body>
    <!-- Navegação reutilizável -->
    <?php include '../templates/gen_menu.php'; ?>

    <!-- Encapsulamento do conteúdo -->
    <section class="form-container">
        <h2>Deletar Usuário</h2>
        <form action="../apis/deletar_us.php" method="POST">
            <label>ID do Usuário:</label>
            <input type="number" name="id" required>
            <button type="submit">Deletar</button>
        </form>
    </section>
</body>
</html>
