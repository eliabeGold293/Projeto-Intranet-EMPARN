<?php
require_once "../config/connection.php";

try {
    $stmt = $pdo->prepare("SELECT id, nome FROM area_atuacao ORDER BY nome ASC");
    $stmt->execute();
    $areas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Lista de Áreas de Atuação</title>
    <link rel="stylesheet" href="style.css">
</head>
<style>
        /* Container principal */
    .container {
        display: flex;
        gap: 20px;
        margin-left: 320px; /* espaço para a sidebar */
        margin-top: 40px;
    }

    /* Tabela */
    .table-container {
        flex: 2;
        padding: 20px;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }
    .table-container h1 {
        margin-top: 0;
        color: #333;
        font-family: Arial, sans-serif;
    }
    .search-box {
        margin-bottom: 20px;
        display: flex;
        gap: 10px;
    }
    .search-box input {
        flex: 1;
        padding: 8px;
        border: 1px solid #ccc;
        border-radius: 4px;
    }
    .search-box button {
        background: #4a90e2;
        color: #fff;
        border: none;
        padding: 8px 15px;
        border-radius: 4px;
        cursor: pointer;
    }
    .search-box button:hover {
        background: #357ab8;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        font-family: Arial, sans-serif;
    }
    th, td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #eee;
    }
    th {
        background: #f4f6f8;
        font-weight: bold;
        color: #555;
    }
    tr:hover {
        background: #f9f9f9;
    }

    /* Botões */
    .btn {
        padding: 6px 12px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        color: #fff;
    }
    .btn-edit { background: #f0ad4e; }
    .btn-delete { background: #d9534f; }

    /* Formulário lateral */
    .form-container {
        flex: 1;
        padding: 20px;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        display: none; /* só aparece quando necessário */
    }
    .form-container h2 {
        margin-top: 0;
        color: #333;
        font-family: Arial, sans-serif;
    }
    .form-container input {
        width: 100%;
        padding: 8px;
        margin-bottom: 10px;
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
    }
    .form-container button:hover {
        background: #357ab8;
    }

    /* Mensagens de feedback */
    .message {
        margin-top: 15px;
        padding: 10px;
        border-radius: 4px;
        font-weight: bold;
    }
    .success { background: #d4edda; color: #155724; }
    .error { background: #f8d7da; color: #721c24; }

</style>
<body>
    <?php include '../templates/gen_menu.php'; ?>

    <div class="container">
        <section class="table-container">
            <h1>Áreas de Atuação cadastradas</h1>

            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Buscar área por nome ou ID...">
                <button onclick="searchArea()">Buscar</button>
            </div>

            <table id="areaTable">
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Ações</th>
                </tr>
                <?php foreach ($areas as $area): ?>
                    <tr>
                        <td><?= htmlspecialchars($area["id"]) ?></td>
                        <td><?= htmlspecialchars($area["nome"]) ?></td>
                        <td>
                            <button class="btn btn-edit" onclick="showEditForm(
                                <?= $area['id'] ?>,
                                '<?= htmlspecialchars($area['nome'], ENT_QUOTES) ?>'
                            )">Editar</button>
                            <button class="btn btn-delete" 
                                onclick="deleteArea(<?= $area['id'] ?>, '<?= htmlspecialchars($area['nome'], ENT_QUOTES) ?>')">
                                Excluir
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <div id="message"></div>
        </section>

        <section class="form-container" id="editFormContainer">
            <h2>Editar Área</h2>
            <form id="editForm">
                <input type="hidden" name="id" id="edit_id">
                <label>Nome:</label>
                <input type="text" name="nome" id="edit_nome" required>
                <button type="submit">Salvar Alterações</button>
            </form>
            <div id="editMessage"></div>
        </section>
    </div>

    <script>
        // Busca na tabela
    function searchArea() {
        const input = document.getElementById("searchInput").value.toLowerCase();
        const rows = document.querySelectorAll("#areaTable tr:not(:first-child)");
        rows.forEach(row => {
            const id = row.cells[0].textContent.toLowerCase();
            const nome = row.cells[1].textContent.toLowerCase();
            row.style.display = (id.includes(input) || nome.includes(input)) ? "" : "none";
        });
    }

    // Mostrar formulário de edição
    function showEditForm(id, nome) {
        document.getElementById("editFormContainer").style.display = "block";
        document.getElementById("edit_id").value = id;
        document.getElementById("edit_nome").value = nome;
    }

    // Submissão do formulário de edição
    document.addEventListener("DOMContentLoaded", () => {
        const editForm = document.getElementById("editForm");
        if (editForm) {
            editForm.addEventListener("submit", function(e) {
                e.preventDefault();
                const formData = new FormData(this);

                fetch("../apis/set_area.php", {
                    method: "POST",
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    const msgDiv = document.getElementById("editMessage");
                    if (data.toLowerCase().includes("sucesso")) {
                        msgDiv.textContent = "Área atualizada com sucesso!";
                        msgDiv.className = "message success";
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        msgDiv.textContent = data;
                        msgDiv.className = "message error";
                    }
                })
                .catch(() => {
                    const msgDiv = document.getElementById("editMessage");
                    msgDiv.textContent = "Erro ao atualizar área.";
                    msgDiv.className = "message error";
                });
            });
        }
    });

    // Excluir área
    function deleteArea(id, nome) {
        // Proteção contra exclusão de nomes específicos
        const protectedAreas = ["GERAL"];
        if (protectedAreas.includes(nome.toUpperCase())) {
            const msgDiv = document.getElementById("message");
            msgDiv.textContent = `A área "${nome}" não pode ser excluída.`;
            msgDiv.className = "message error";
            return;
        }

        if (confirm(`Deseja realmente excluir a área "${nome}"?`)) {
            fetch("../apis/deletar_area.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "id=" + encodeURIComponent(id)
            })
            .then(response => response.text())
            .then(data => {
                const msgDiv = document.getElementById("message");
                if (data.toLowerCase().includes("sucesso")) {
                    msgDiv.textContent = `Área "${nome}" excluída com sucesso!`;
                    msgDiv.className = "message success";
                    setTimeout(() => location.reload(), 1000);
                } else {
                    msgDiv.textContent = data;
                    msgDiv.className = "message error";
                }
            })
            .catch(() => {
                const msgDiv = document.getElementById("message");
                msgDiv.textContent = "Erro ao excluir área.";
                msgDiv.className = "message error";
            });
        }
    }

    </script>
</body>
</html>
