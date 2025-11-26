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
                            <h5 class="mb-0">Classes cadastradas</h5>
                        </div>
                        <div class="card-body">
                            <!-- Busca -->
                            <form class="d-flex mb-3">
                                <input type="text" id="searchInput" class="form-control me-2" placeholder="Buscar classe por nome ou grau de acesso...">
                                <button type="button" class="btn btn-primary" onclick="searchClass()">Buscar</button>
                            </form>

                            <!-- Tabela -->
                            <div class="table-responsive">
                                <table id="classTable" class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>ID</th>
                                            <th>Nome</th>
                                            <th>Grau de Acesso</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($classes as $class): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($class["id"]) ?></td>
                                                <td><?= htmlspecialchars($class["nome"]) ?></td>
                                                <td><?= htmlspecialchars($class["grau_acesso"]) ?></td>
                                                <td>
                                                    <?php if (strtoupper($class["nome"]) !== "CONTROLE"): ?>
                                                        <button class="btn btn-warning btn-sm me-2" onclick="showEditForm(
                                                            <?= $class['id'] ?>,
                                                            '<?= htmlspecialchars($class['nome'], ENT_QUOTES) ?>',
                                                            '<?= htmlspecialchars($class['grau_acesso'], ENT_QUOTES) ?>'
                                                        )">✏️ Editar</button>
                                                        <button class="btn btn-danger btn-sm" 
                                                            onclick="deleteClass(<?= $class['id'] ?>, '<?= htmlspecialchars($class['nome'], ENT_QUOTES) ?>')">
                                                            🗑️ Excluir
                                                        </button>
                                                    <?php else: ?>
                                                        <span class="text-muted">Protegida</span>
                                                    <?php endif; ?>
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
                            <h5 class="mb-0">Editar Classe</h5>
                        </div>
                        <div class="card-body">
                            <form id="editForm" class="needs-validation" novalidate>
                                <input type="hidden" name="id" id="edit_id">
                                <div class="mb-3">
                                    <label class="form-label">Nome</label>
                                    <input type="text" name="nome" id="edit_nome" class="form-control" required>
                                    <div class="invalid-feedback">Informe o nome da classe.</div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Grau de Acesso (1 a 4)</label>
                                    <input type="number" name="grau_acesso" id="edit_grau" class="form-control" min="1" max="4" required>
                                    <div class="invalid-feedback">Informe um grau de acesso válido.</div>
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
        function searchClass() {
            const input = document.getElementById("searchInput").value.toLowerCase();
            const rows = document.querySelectorAll("#classTable tbody tr");
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
                    if (!this.checkValidity()) {
                        this.classList.add('was-validated');
                        return;
                    }
                    const formData = new FormData(this);

                    fetch("../apis/set_classes.php", {
                        method: "POST",
                        body: formData
                    })
                    .then(response => response.text())
                    .then(data => {
                        const msgDiv = document.getElementById("editMessage");
                        if (data.toLowerCase().includes("sucesso")) {
                            msgDiv.innerHTML = '<div class="alert alert-success">Classe atualizada com sucesso!</div>';
                            setTimeout(() => location.reload(), 1000);
                        } else {
                            msgDiv.innerHTML = '<div class="alert alert-danger">' + data + '</div>';
                        }
                    })
                    .catch(() => {
                        document.getElementById("editMessage").innerHTML = '<div class="alert alert-danger">Erro ao atualizar classe.</div>';
                    });
                });
            }
        });

        // Excluir classe
        function deleteClass(id, nome) {
            if (nome.toUpperCase() === "CONTROLE") {
                document.getElementById("message").innerHTML = `<div class="alert alert-danger">A classe ${nome} não pode ser excluída.</div>`;
                return;
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
                        msgDiv.innerHTML = '<div class="alert alert-success">Classe excluída com sucesso!</div>';
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        msgDiv.innerHTML = '<div class="alert alert-danger">' + data + '</div>';
                    }
                })
                .catch(() => {
                    document.getElementById("message").innerHTML = '<div class="alert alert-danger">Erro ao excluir classe.</div>';
                });
            }
        }
    </script>
</body>
</html