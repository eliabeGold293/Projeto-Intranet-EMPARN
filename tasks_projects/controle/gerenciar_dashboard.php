<?php 
require_once "../config/connection.php";
include '../templates/gen_menu.php';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Dashboard</title>
    <!-- Bootstrap opcional -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        /* ===== Reset básico ===== */
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background-color: #f4f6f8;
            color: #333;
            margin: 0;
        }

        /* Área principal da página */
        .main-content {
            margin-left: 320px; /* espaço para a sidebar (250px + respiro) */
            padding: 20px;
        }

        /* ===== Título principal ===== */
        h3 {
            font-size: 1.8rem;
            font-weight: bold;
            color: #0046a0;
            margin-bottom: 10px;
        }

        hr {
            border: none;
            height: 2px;
            background: linear-gradient(90deg, #003e4d, #0046a0);
            margin-bottom: 30px;
        }

        /* ===== Formulário ===== */
        form {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            max-width: 600px;
        }

        form label {
            font-weight: bold;
            color: #333;
        }

        form input[type="text"],
        form input[type="url"] {
            border: 1px solid #ccc;
            border-radius: 4px;
            padding: 8px;
            width: 100%;
        }

        form input[type="color"] {
            width: 60px;
            height: 40px;
            padding: 0;
            border: none;
            cursor: pointer;
        }

        /* Botão de envio */
        form button {
            margin-top: 10px;
            background: #28a745;
            border: none;
            padding: 10px 15px;
            color: #fff;
            border-radius: 4px;
            font-weight: bold;
            transition: background 0.3s ease;
        }

        form button:hover {
            background: #218838;
        }

        /* ===== Tabela ===== */
        h4 {
            margin-top: 40px;
            font-size: 1.5rem;
            color: #0046a0;
        }

        table {
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            margin-top: 20px;
        }

        thead th {
            background-color: #d4edda;
            color: #155724;
            font-weight: bold;
        }

        td {
            vertical-align: middle;
        }

        td a {
            word-break: break-word;
        }

        /* Cor do quadradinho */
        td div {
            border: 1px solid #ccc;
        }

        /* Botão excluir */
        .btn-danger.btn-sm {
            padding: 5px 10px;
            font-size: 0.85rem;
            border-radius: 4px;
        }
    </style>
</head>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const form = document.querySelector("form");
    const tbody = document.querySelector("table tbody");

    // Intercepta envio do formulário
    form.addEventListener("submit", async (e) => {
        e.preventDefault();

        const formData = new FormData(form);

        try {
            const response = await fetch("../apis/salvar_cards.php", {
                method: "POST",
                body: formData
            });

            const text = await response.text();
            console.log("Resposta bruta:", text); // 👈 Aqui você vê o que o PHP está retornando

            const result = JSON.parse(text);

            if (result.status === "success") {
                const newRow = document.createElement("tr");
                newRow.innerHTML = `
                    <td>${result.titulo}</td>
                    <td><div style="width:30px; height:30px; background:${result.cor}; border-radius:5px;"></div></td>
                    <td><a href="${result.link}" target="_blank">${result.link}</a></td>
                    <td><button class="btn btn-danger btn-sm excluir" data-id="${result.id}">Excluir</button></td>
                `;
                tbody.prepend(newRow);
                form.reset();
            } else {
                alert("Erro ao adicionar: " + result.message);
            }
        } catch (error) {
            alert("Erro ao interpretar JSON: " + error.message);
        }
    });

    // Delegação para excluir
    tbody.addEventListener("click", async (e) => {
        if (e.target.classList.contains("excluir")) {
            const row = e.target.closest("tr");
            const id = e.target.dataset.id;

            if (!id) return;

            try {
                const response = await fetch("../apis/deletar_card.php?id=" + id);
                const text = await response.text();
                console.log("Resposta bruta (exclusão):", text);

                const result = JSON.parse(text);

                if (result.status === "success") {
                    row.remove();
                } else {
                    alert("Erro ao excluir: " + result.message);
                }
            } catch (error) {
                alert("Erro ao interpretar JSON: " + error.message);
            }
        }
    });
});
</script>


<body>
    <div class="main-content">
        <h3>📊 Gerenciar Dashboard</h3>
        <hr>

        <!-- Formulário de criação de card -->
        <form action="../apis/salvar_cards.php" method="POST" class="mb-5">
            <div class="mb-3">
                <label for="titulo" class="form-label">Nome do Card:</label>
                <input type="text" class="form-control" name="titulo" required>
            </div>
            <div class="mb-3">
                <label for="cor" class="form-label">Cor de Fundo:</label><br>
                <input type="color" class="form-control form-control-color" name="cor" value="#006400" required>
            </div>
            <div class="mb-3">
                <label for="link" class="form-label">Link de Destino:</label>
                <input type="url" class="form-control" name="link" placeholder="https://www.exemplo.com" required>
            </div>
            <button type="submit" class="btn btn-success">Adicionar Card</button>
        </form>

        <!-- Listagem dos cards -->
        <h4>🗂️ Cards Atuais</h4>
        <table class="table table-striped table-bordered">
            <thead class="table-success">
                <tr>
                    <th>Nome</th>
                    <th>Cor</th>
                    <th>Link</th>
                    <th>Ação</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT * FROM dashboard ORDER BY id DESC";
                $stmt = $pdo->query($sql);
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (count($result) > 0):
                    foreach ($result as $row):
                ?>
                <tr>
                    <td><?= htmlspecialchars($row['titulo']) ?></td>
                    <td>
                        <div style="width:30px; height:30px; background:<?= htmlspecialchars($row['cor']) ?>; border-radius:5px;"></div>
                    </td>
                    <td>
                        <a href="<?= htmlspecialchars($row['link']) ?>" target="_blank">
                            <?= htmlspecialchars($row['link']) ?>
                        </a>
                    </td>
                    <td>
                        <a href="../apis/deletar_card.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm">Excluir</a>
                    </td>
                </tr>
                <?php
                    endforeach;
                else:
                ?>
                <tr>
                    <td colspan="4" class="text-center text-muted">Nenhum quadradinho cadastrado ainda.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
