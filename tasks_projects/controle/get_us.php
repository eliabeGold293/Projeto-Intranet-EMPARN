<?php
require_once "../config/connection.php";

try {
    // Agora traz também os nomes da classe e da área
    $stmt = $pdo->prepare("
        SELECT u.id,
               u.nome,
               u.email,
               c.nome AS classe_nome,
               a.nome AS area_nome,
               u.data_criacao,
               u.data_modificacao
        FROM usuario u
        JOIN classe_usuario c ON u.classe_id = c.id
        JOIN area_atuacao a   ON u.area_id   = a.id
        ORDER BY u.id ASC
    ");
    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro: " . $e->getMessage());
}

// Carregar classes e áreas para os selects
try {
    $stmt = $pdo->prepare("SELECT id, nome FROM classe_usuario ORDER BY nome ASC");
    $stmt->execute();
    $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("SELECT id, nome FROM area_atuacao ORDER BY nome ASC");
    $stmt->execute();
    $areas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro ao carregar selects: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Lista de Usuários</title>
    <link rel="stylesheet" href="style.css">
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
            display: none; /* escondido até clicar em editar */
        }
        .form-container input, .form-container select {
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
</head>
<body>
    <?php include '../templates/gen_menu.php'; ?>

    <div class="container">
        <section class="table-container">
            <h1>Usuários cadastrados</h1>
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Buscar usuário por nome ou email...">
                <button onclick="searchUser()">Buscar</button>
            </div>
            <table id="userTable">
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Classe</th>
                    <th>Área</th>
                    <th>Criado Em</th>
                    <th>Modificado EM</th>
                    <th>Ações</th>
                </tr>
                <?php foreach ($usuarios as $us): ?>
                    <tr>
                        <td><?= htmlspecialchars($us["id"]) ?></td>
                        <td><?= htmlspecialchars($us["nome"]) ?></td>
                        <td><?= htmlspecialchars($us["email"]) ?></td>
                        <td><?= htmlspecialchars($us["classe_nome"]) ?></td>
                        <td><?= htmlspecialchars($us["area_nome"]) ?></td>
                        <td><?= htmlspecialchars($us["data_criacao"]) ?></td>
                        <td><?= htmlspecialchars($us["data_modificacao"]) ?></td>
                        <td>
                            <button class="btn btn-edit" onclick="showEditForm(
                                <?= $us['id'] ?>,
                                '<?= htmlspecialchars($us['nome'], ENT_QUOTES) ?>',
                                '<?= htmlspecialchars($us['email'], ENT_QUOTES) ?>',
                                '<?= htmlspecialchars($us['classe_id'], ENT_QUOTES) ?>',
                                '<?= htmlspecialchars($us['area_id'], ENT_QUOTES) ?>'
                            )">Editar</button>
                            <button class="btn btn-delete" onclick="deleteUser(<?= $us['id'] ?>)">Excluir</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <div id="message"></div>
        </section>

        <section class="form-container" id="editFormContainer">
            <h2>Editar Usuário</h2>
            <form id="editForm">
                <input type="hidden" name="id" id="edit_id">
                <label>Nome:</label>
                <input type="text" name="nome" id="edit_nome" required>
                <label>Email:</label>
                <input type="email" name="email" id="edit_email" required>
                <label>Senha (deixe em branco para não alterar):</label>
                <input type="password" name="senha" id="edit_senha">
                <label>Classe:</label>
                <select name="classe_id" id="edit_classe">
                    <?php foreach ($classes as $c): ?>
                        <option value="<?= htmlspecialchars($c['id']) ?>">
                            <?= htmlspecialchars($c['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label>Área:</label>
                <select name="area_id" id="edit_area">
                    <?php foreach ($areas as $a): ?>
                        <option value="<?= htmlspecialchars($a['id']) ?>">
                            <?= htmlspecialchars($a['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit">Salvar Alterações</button>

            </form>
            <div id="editMessage"></div>
        </section>
    </div>

    <script>
        // Função de busca na tabela
        function searchUser() {
            const input = document.getElementById("searchInput").value.toLowerCase();
            const rows = document.querySelectorAll("#userTable tr:not(:first-child)");

            rows.forEach(row => {
                const nome = row.cells[1].textContent.toLowerCase();
                const email = row.cells[2].textContent.toLowerCase();
                row.style.display = (nome.includes(input) || email.includes(input)) ? "" : "none";
            });
        }

        // Exibir formulário de edição ao lado da tabela
        function showEditForm(id, nome, email, classe, area) {
            const formContainer = document.getElementById("editFormContainer");
            formContainer.style.display = "block";

            document.getElementById("edit_id").value = id;
            document.getElementById("edit_nome").value = nome;
            document.getElementById("edit_email").value = email;
            document.getElementById("edit_senha").value = "";
            document.getElementById("edit_classe").value = classe;
            document.getElementById("edit_area").value = area;
        }

        // Submissão do formulário de edição via fetch
        document.addEventListener("DOMContentLoaded", () => {
            const editForm = document.getElementById("editForm");
            if (editForm) {
                editForm.addEventListener("submit", function(e) {
                    e.preventDefault();
                    const formData = new FormData(this);

                    fetch("../apis/set_us.php", {
                        method: "POST",
                        body: formData
                    })
                    .then(response => response.text())
                    .then(data => {
                        const msgDiv = document.getElementById("editMessage");
                        if (data.toLowerCase().includes("sucesso")) {
                            msgDiv.textContent = "Usuário atualizado com sucesso!";
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
                        msgDiv.textContent = "Erro ao atualizar usuário.";
                        msgDiv.className = "message error";
                    });
                });
            }
        });

        // Função para excluir usuário
        function deleteUser(id) {
            if (confirm("Deseja realmente excluir o usuário?")) {
                fetch("../apis/deletar_us.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: "id=" + encodeURIComponent(id)
                })
                .then(response => response.text())
                .then(data => {
                    const msgDiv = document.getElementById("message");
                    if (data.includes("sucesso")) {
                        msgDiv.textContent = "Usuário excluído com sucesso";
                        msgDiv.className = "message success";

                        // Remove a linha da tabela correspondente
                        const rows = document.querySelectorAll("#userTable tr");
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
                    msgDiv.textContent = "Erro ao excluir usuário";
                    msgDiv.className = "message error";
                });
            }
        }
    </script>
</body>
</html>

