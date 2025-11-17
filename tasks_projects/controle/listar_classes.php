<?php
require_once "../config/connection.php";

try {
    $stmt = $pdo->prepare("SELECT id, nome, grau_acesso FROM classe_usuario ORDER BY nome ASC");
    $stmt->execute();
    $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Lista de Classes</title>
    <link rel="stylesheet" href="style.css">
</head>
<style>
    .container {
    display: flex;
    gap: 20px;
    margin-left: 320px;
    margin-top: 40px;
    }
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
    .btn {
        padding: 6px 12px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        color: #fff;
    }
    .btn-edit { background: #f0ad4e; }
    .btn-delete { background: #d9534f; }
    .form-container {
        flex: 1;
        padding: 20px;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        display: none;
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
            <h1>Classes cadastradas</h1>

            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Buscar classe por nome ou grau de acesso...">
                <button onclick="searchClass()">Buscar</button>
            </div>

            <table id="classTable">
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Grau de Acesso</th>
                    <th>Ações</th>
                </tr>
                <?php foreach ($classes as $class): ?>
                    <tr>
                        <td><?= htmlspecialchars($class["id"]) ?></td>
                        <td><?= htmlspecialchars($class["nome"]) ?></td>
                        <td><?= htmlspecialchars($class["grau_acesso"]) ?></td>
                        <td>
                            <?php if (strtoupper($class["nome"]) !== "CONTROLE"): ?>
                                <button class="btn btn-edit" onclick="showEditForm(
                                    <?= $class['id'] ?>,
                                    '<?= htmlspecialchars($class['nome'], ENT_QUOTES) ?>',
                                    '<?= htmlspecialchars($class['grau_acesso'], ENT_QUOTES) ?>'
                                )">Editar</button>
                                <button class="btn btn-delete" 
                                    onclick="deleteClass(<?= $class['id'] ?>, '<?= htmlspecialchars($class['nome'], ENT_QUOTES) ?>')">
                                    Excluir
                                </button>
                            <?php else: ?>
                                <!-- Nenhum botão para a classe CONTROLE -->
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>

            </table>
            <div id="message"></div>
        </section>

        <section class="form-container" id="editFormContainer">
            <h2>Editar Classe</h2>
            <form id="editForm">
                <input type="hidden" name="id" id="edit_id">
                <label>Nome:</label>
                <input type="text" name="nome" id="edit_nome" required>
                <label>Grau de Acesso (1 a 4):</label>
                <input type="number" name="grau_acesso" id="edit_grau" min="1" max="4" required>
                <button type="submit">Salvar Alterações</button>
            </form>
            <div id="editMessage"></div>
        </section>
    </div>

    <script>
        // Busca na tabela
    function searchClass() {
        const input = document.getElementById("searchInput").value.toLowerCase();
        const rows = document.querySelectorAll("#classTable tr:not(:first-child)");
        rows.forEach(row => {
            const nome = row.cells[1].textContent.toLowerCase();
            const grau = row.cells[2].textContent.toLowerCase();
            row.style.display = (nome.includes(input) || grau.includes(input)) ? "" : "none";
        });
    }

    // Mostrar formulário de edição
    function showEditForm(id, nome, grau) {
        document.getElementById("editFormContainer").style.display = "block";
        document.getElementById("edit_id").value = id;
        document.getElementById("edit_nome").value = nome;
        document.getElementById("edit_grau").value = grau;
    }

    // Submissão do formulário de edição
    document.addEventListener("DOMContentLoaded", () => {
        const editForm = document.getElementById("editForm");
        if (editForm) {
            editForm.addEventListener("submit", function(e) {
                e.preventDefault();
                const formData = new FormData(this);

                fetch("../apis/set_classes.php", {
                    method: "POST",
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    const msgDiv = document.getElementById("editMessage");
                    if (data.toLowerCase().includes("sucesso")) {
                        msgDiv.textContent = "Classe atualizada com sucesso!";
                        msgDiv.className = "message success";

                    // Aguarda 1 segundo para o usuário ver a mensagem e recarrega a página
                    setTimeout(() => {
                        location.reload();
                    }, 1000);

                    } else {
                        msgDiv.textContent = data;
                        msgDiv.className = "message error";
                    }
                })
                .catch(() => {
                    const msgDiv = document.getElementById("editMessage");
                    msgDiv.textContent = "Erro ao atualizar classe.";
                    msgDiv.className = "message error";
                });
            });
        }
    });

    function deleteClass(id, nome) {
        // Verifica se a classe é "CONTROLE"
        if (nome.toUpperCase() === "CONTROLE") {
            const msgDiv = document.getElementById("message");
            msgDiv.textContent = `A classe ${nome} não pode ser excluída.`;
            msgDiv.className = "message error";
            return; // não prossegue com o fetch
        }

        if (confirm("Deseja realmente excluir a classe?")) {
            fetch("../apis/deletar_classes.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "id=" + encodeURIComponent(id)
            })
            .then(response => response.text())
            .then(data => {
                const msgDiv = document.getElementById("message");
                if (data.toLowerCase().includes("sucesso")) {
                    msgDiv.textContent = "Classe excluída com sucesso!";
                    msgDiv.className = "message success";

                    // Remove a linha da tabela correspondente
                    const rows = document.querySelectorAll("#classTable tr");
                    rows.forEach(row => {
                        if (row.cells[0] && row.cells[0].textContent == id) {
                            row.remove();
                        }
                    });
                } else {
                    msgDiv.textContent = data;
                    msgDiv.className = "message error";
                }
            })
            .catch(() => {
                const msgDiv = document.getElementById("message");
                msgDiv.textContent = "Erro ao excluir classe.";
                msgDiv.className = "message error";
            });
        }
    }
    </script>
</body>
</html>
