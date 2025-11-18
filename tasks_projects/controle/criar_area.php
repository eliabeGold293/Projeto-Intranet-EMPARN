<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Criar Área de Atuação</title>
    <link rel="stylesheet" href="style.css"> <!-- CSS separado -->
</head>
<style>
    .form-container {
        margin-left: 320px;
        padding: 30px; /* espaçamento interno uniforme */
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        max-width: 600px;
        box-sizing: border-box;
    }

    .form-container h2 {
        margin-top: 0;
        margin-bottom: 20px;
        color: #333;
        font-family: Arial, sans-serif;
        text-align: left; /* título alinhado à esquerda */
    }

    .form-container form {
        display: flex;
        flex-direction: column;
    }

    .form-container label {
        font-weight: bold;
        margin-bottom: 5px;
        color: #333;
    }

    .form-container input,
    .form-container select {
        width: 100%;
        padding: 10px;
        margin-bottom: 20px;
        border: 1px solid #ccc;
        border-radius: 4px;
        font-size: 14px;
        box-sizing: border-box;
    }

    .form-container button {
        background: #4a90e2;
        color: #fff;
        border: none;
        padding: 10px 20px;
        border-radius: 4px;
        cursor: pointer;
        transition: background 0.3s ease;
        align-self: flex-start; /* botão alinhado à esquerda */
    }

    .form-container button:hover {
        background: #357ab8;
    }

    .message {
        margin-top: 15px;
        padding: 10px;
        border-radius: 4px;
        font-weight: bold;
    }

    .success {
        background: #d4edda;
        color: #155724;
    }

    .error {
        background: #f8d7da;
        color: #721c24;
    }


</style>
<body>
    <!-- Navegação reutilizável -->
    <?php include '../templates/gen_menu.php'; ?>

    <!-- Encapsulamento do conteúdo -->
    <section class="form-container">
        <h2>Criar Área de Atuação</h2>
        <form id="createAreaForm" action="../apis/criar_area.php" method="POST">
            <label>Nome da Área:</label>
            <input type="text" name="nome" required>

            <button type="submit">Salvar</button>
        </form>

        <div id="message"></div>
    </section>

    <script>
        document.getElementById("createAreaForm").addEventListener("submit", function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch("../apis/criar_area.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                const msgDiv = document.getElementById("message");
                if (data.toLowerCase().includes("sucesso")) {
                    msgDiv.textContent = "Área criada com sucesso!";
                    msgDiv.className = "message success";
                    this.reset();
                } else {
                    msgDiv.textContent = "Erro ao criar área: " + data;
                    msgDiv.className = "message error";
                }
            })
            .catch(() => {
                const msgDiv = document.getElementById("message");
                msgDiv.textContent = "Erro ao criar área.";
                msgDiv.className = "message error";
            });
        });
</script>

</body>
</html>
