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
    .form-container {
        margin-left: 320px;
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
    <?php include '../templates/gen_menu.php'; ?>

    <section class="form-container">
        <h2>Cadastro de Usuário</h2>
        <form id="userForm">
            <label>Nome:</label>
            <input type="text" name="nome" required>

            <label>Email:</label>
            <input type="email" name="email" required>

            <label>Senha:</label>
            <input type="password" name="senha" required>

            <label>Classe:</label>
            <select name="classe_id" id="classe_id"> 
                <?php foreach ($classe_usuario as $class_us): ?>
                    <option value="<?= htmlspecialchars($class_us['id']) ?>">
                        <?= htmlspecialchars($class_us['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label>Área:</label>
            <select name="area_id" id="area_id">
                <?php foreach ($areas as $area): ?>
                    <option value="<?= htmlspecialchars($area['id']) ?>">
                        <?= htmlspecialchars($area['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit">Salvar</button>
        </form>

        <div id="message"></div>
    </section>

    <script>
        document.getElementById("userForm").addEventListener("submit", function(e) {
            e.preventDefault(); // evita recarregar a página

            const formData = new FormData(this);

            fetch("../apis/criar_us.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                const msgDiv = document.getElementById("message");
                if (data.toLowerCase().includes("sucesso")) {
                    msgDiv.textContent = "Usuário criado com sucesso!";
                    msgDiv.className = "message success";
                    this.reset(); // limpa o formulário
                } else {
                    msgDiv.textContent = "Erro ao criar usuário: " + data;
                    msgDiv.className = "message error";
                }
            })
            .catch(() => {
                const msgDiv = document.getElementById("message");
                msgDiv.textContent = "Erro ao criar usuário.";
                msgDiv.className = "message error";
            });
        });
    </script>
</body>
</html>
