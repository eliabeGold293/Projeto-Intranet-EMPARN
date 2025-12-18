<?php
require_once __DIR__ . '/../config/connection.php';

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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <style>
        body {
            background-color: #eef1f4;
            display: flex;
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
        }

        .main-content {
            flex: 1;
            padding: 40px;
            margin-left: 250px;
            max-width: 1600px;
            margin-right: auto;
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 20px;
            }
        }

        /* Título principal padrão */
        .page-title {
            font-size: 2rem;
            font-weight: 700;
            color: #0046a0;
            margin-bottom: 25px;
        }

        /* Card moderno igual ao da interface de usuários */
        .card-modern {
            background: #fff;
            border-radius: 12px;
            padding: 28px;
            border: none;
            box-shadow: 0 4px 14px rgba(0,0,0,0.08);
        }

        .card-header-modern {
            background: transparent !important;
            border-bottom: 2px solid #e6e9ed;
            padding-bottom: 12px;
            margin-bottom: 18px;
        }

        /* Labels iguais à interface anterior */
        .form-label {
            font-weight: 600;
            color: #003b87;
        }

        /* Inputs iguais */
        .form-control,
        .form-select {
            border-radius: 6px;
            padding: 10px;
        }

        /* Tabela padrão */
        .table-responsive {
            max-width: 1250px;
            margin: 0 auto;
        }

        .table thead th {
            font-weight: 700;
            background: #f2f4f7 !important;
        }

        .table-hover tbody tr:hover {
            background-color: #f0f4fa !important;
        }

        .btn-sm {
            font-size: .8rem;
            padding: 5px 10px;
        }

        #editFormContainer {
            display: none;
        }
    </style>
</head>
<body>

    <?php include __DIR__ . '/../templates/gen_menu.php'; ?>

    <main class="main-content">

        <!-- Título igual ao da tela de usuários -->
        <h2 class="page-title">
            <i class="bi bi-shield-lock"></i> Lista de Classes
        </h2>

        <div class="container-fluid">
            <div class="row">

                <!-- TABELA -->
                <div class="col-lg-10 mb-4">
                    <div class="card-modern">

                        <div class="card-header-modern">
                            <h4 class="mb-0">Classes cadastradas</h4>
                        </div>

                        <!-- Busca -->
                        <form class="d-flex mb-3">
                            <input type="text" id="searchInput" class="form-control me-2" placeholder="Buscar classe por nome ou grau de acesso...">
                            <button type="button" class="btn btn-primary" onclick="searchClass()">Buscar</button>
                        </form>

                        <!-- Tabela -->
                        <div class="table-responsive">
                            <table id="classTable" class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nome</th>
                                        <th>Grau de Acesso</th>
                                        <th style="width:120px;">Ações</th>
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
                                                    )"><i class="bi bi-pencil-square"></i></button>

                                                    <button class="btn btn-danger btn-sm"
                                                        onclick="deleteClass(<?= $class['id'] ?>, '<?= htmlspecialchars($class['nome'], ENT_QUOTES) ?>')">
                                                        <i class="bi bi-trash-fill"></i>
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

                <!-- FORMULÁRIO DE EDIÇÃO -->
                <div class="col-lg-4 mb-4">
                    <div class="card-modern" id="editFormContainer">

                        <div class="card-header-modern">
                            <h4 class="mb-0">Editar Classe</h4>
                        </div>

                        <form id="editForm" class="needs-validation" novalidate>
                            <input type="hidden" name="id" id="edit_id">

                            <div class="mb-3">
                                <label class="form-label">Nome</label>
                                <input type="text" name="nome" id="edit_nome" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Grau de Acesso (1 a 4)</label>
                                <input type="number" name="grau_acesso" id="edit_grau" class="form-control" min="1" max="4" required>
                            </div>

                            <button type="submit" class="btn btn-success">
                                Salvar Alterações
                            </button>

                        </form>

                        <div id="editMessage" class="mt-3"></div>

                    </div>
                </div>

            </div>
        </div>

    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function searchClass() {
            const input = document.getElementById("searchInput").value.toLowerCase();
            const rows = document.querySelectorAll("#classTable tbody tr");
            rows.forEach(row => {
                const nome = row.cells[1].textContent.toLowerCase();
                const grau = row.cells[2].textContent.toLowerCase();
                row.style.display = (nome.includes(input) || grau.includes(input)) ? "" : "none";
            });
        }

        function showEditForm(id, nome, grau) {
            document.getElementById("editFormContainer").style.display = "block";
            document.getElementById("edit_id").value = id;
            document.getElementById("edit_nome").value = nome;
            document.getElementById("edit_grau").value = grau;
        }

        document.addEventListener("DOMContentLoaded", () => {
            const editForm = document.getElementById("editForm");
            editForm.addEventListener("submit", function(e) {
                e.preventDefault();
                if (!this.checkValidity()) {
                    this.classList.add('was-validated');
                    return;
                }

                const formData = new FormData(this);

                fetch("atualizar-info-classe", {
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
        });

        function deleteClass(id, nome) {
            if (nome.toUpperCase() === "CONTROLE") {
                document.getElementById("message").innerHTML = `<div class="alert alert-danger">A classe ${nome} não pode ser excluída.</div>`;
                return;
            }

            if (confirm("Deseja realmente excluir a classe?")) {
                fetch("deletar-classe-usuario", {
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
</html>
