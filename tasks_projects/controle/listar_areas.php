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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f4f6f8;
            display: flex;
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
        }

        .main-content {
            flex: 1;
            padding: 30px;
            margin-left: 250px; /* espaço para o menu lateral */
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 20px;
            }
        }

        .card {
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }

        #editFormContainer {
            display: none; /* só aparece quando necessário */
        }
    </style>
</head>
<body>
    <!-- Menu reutilizável -->
    <?php include '../templates/gen_menu.php'; ?>

    <!-- Conteúdo principal -->
    <main class="main-content">
        <div class="container-fluid">
            <div class="row">
                <!-- Tabela -->
                <div class="col-lg-8 mb-4">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Áreas de Atuação cadastradas</h5>
                        </div>
                        <div class="card-body">
                            <!-- Busca -->
                            <form class="d-flex mb-3">
                                <input type="text" id="searchInput" class="form-control me-2" placeholder="Buscar área por nome ou ID...">
                                <button type="button" class="btn btn-primary" onclick="searchArea()">Buscar</button>
                            </form>

                            <!-- Tabela -->
                            <div class="table-responsive">
                                <table id="areaTable" class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>ID</th>
                                            <th>Nome</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($areas as $area): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($area["id"]) ?></td>
                                                <td><?= htmlspecialchars($area["nome"]) ?></td>
                                                <td>
                                                    <button class="btn btn-warning btn-sm me-2" onclick="showEditForm(
                                                        <?= $area['id'] ?>,
                                                        '<?= htmlspecialchars($area['nome'], ENT_QUOTES) ?>'
                                                    )">✏️ Editar</button>
                                                    <button class="btn btn-danger btn-sm" 
                                                        onclick="deleteArea(<?= $area['id'] ?>, '<?= htmlspecialchars($area['nome'], ENT_QUOTES) ?>')">
                                                        🗑️ Excluir
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div id="message" class="mt-3"></div>
                        </div>
                    </div>
                </div>

                <!-- Formulário de edição -->
                <div class="col-lg-4 mb-4">
                    <div class="card" id="editFormContainer">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="mb-0">Editar Área</h5>
                        </div>
                        <div class="card-body">
                            <form id="editForm" class="needs-validation" novalidate>
                                <input type="hidden" name="id" id="edit_id">
                                <div class="mb-3">
                                    <label class="form-label">Nome</label>
                                    <input type="text" name="nome" id="edit_nome" class="form-control" required>
                                    <div class="invalid-feedback">Informe o nome da área.</div>
                                </div>
                                <button type="submit" class="btn btn-success">Salvar Alterações</button>
                            </form>
                            <div id="editMessage" class="mt-3"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Busca na tabela
        function searchArea() {
            const input = document.getElementById("searchInput").value.toLowerCase();
            const rows = document.querySelectorAll("#areaTable tbody tr");
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
                    if (!this.checkValidity()) {
                        this.classList.add('was-validated');
                        return;
                    }
                    const formData = new FormData(this);

                    fetch("../apis/set_area.php", {
                        method: "POST",
                        body: formData
                    })
                    .then(response => response.text())
                    .then(data => {
                        const msgDiv = document.getElementById("editMessage");
                        if (data.toLowerCase().includes("sucesso")) {
                            msgDiv.innerHTML = '<div class="alert alert-success">Área atualizada com sucesso!</div>';
                            setTimeout(() => location.reload(), 1000);
                        } else {
                            msgDiv.innerHTML = '<div class="alert alert-danger">' + data + '</div>';
                        }
                    })
                    .catch(() => {
                        document.getElementById("editMessage").innerHTML = '<div class="alert alert-danger">Erro ao atualizar área.</div>';
                    });
                });
            }
        });

        // Excluir área
        function deleteArea(id, nome) {
            const protectedAreas = ["GERAL"];
            if (protectedAreas.includes(nome.toUpperCase())) {
                document.getElementById("message").innerHTML = `<div class="alert alert-danger">A área "${nome}" não pode ser excluída.</div>`;
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
                        msgDiv.innerHTML = `<div class="alert alert-success">Área "${nome}" excluída com sucesso!</div>`;
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        msgDiv.innerHTML = '<div class="alert alert-danger">' + data + '</div>';
                    }
                })
                .catch(() => {
                    document.getElementById("message").innerHTML = '<div class="alert alert-danger">Erro ao excluir área.</div>';
                });
            }
        }
    </script>
</body>
</html>
