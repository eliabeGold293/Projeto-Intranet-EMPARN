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
    <title>Áreas de Atuação</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap + Ícones -->
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

        /* Título padronizado */
        .page-title {
            font-size: 2rem;
            font-weight: 700;
            color: #0046a0;
            margin-bottom: 25px;
        }

        /* Card moderno padrão */
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

        .form-label {
            font-weight: 600;
            color: #003b87;
        }

        .form-control {
            border-radius: 6px;
            padding: 10px;
        }

        /* Tabela moderna */
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

        #editFormContainer {
            display: none;
        }

        .btn-sm {
            font-size: .8rem;
            padding: 5px 10px;
        }

    </style>

</head>
<body>

    <?php include '../templates/gen_menu.php'; ?>

    <main class="main-content">

        <h2 class="page-title">
            <i class="bi bi-stack"></i> Áreas de Atuação
        </h2>

        <div class="container-fluid">
            <div class="row">

                <!-- LISTA -->
                <div class="col-lg-10 mb-4">
                    <div class="card-modern">

                        <div class="card-header-modern">
                            <h4 class="mb-0">Áreas cadastradas</h4>
                        </div>

                        <!-- Barra de Busca -->
                        <form class="d-flex mb-3">
                            <input type="text" id="searchInput" class="form-control me-2" placeholder="Buscar por nome ou ID...">
                            <button type="button" class="btn btn-primary" onclick="searchArea()">Buscar</button>
                        </form>

                        <!-- TABELA -->
                        <div class="table-responsive">
                            <table id="areaTable" class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nome</th>
                                        <th style="width:120px;">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($areas as $area): ?>
                                        <tr>
                                            <td><?= $area["id"] ?></td>
                                            <td><?= htmlspecialchars($area["nome"]) ?></td>
                                            <td>
                                                <button class="btn btn-warning btn-sm me-2"
                                                    onclick="showEditForm(<?= $area['id'] ?>, '<?= htmlspecialchars($area['nome'], ENT_QUOTES) ?>')">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>

                                                <button class="btn btn-danger btn-sm"
                                                    onclick="deleteArea(<?= $area['id'] ?>)">
                                                    <i class="bi bi-trash-fill"></i>
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

                <!-- FORM DE EDIÇÃO PADRONIZADO -->
                <div class="col-lg-4 mb-4">
                    <div id="editFormContainer" class="card-modern">

                        <div class="card-header-modern d-flex justify-content-between align-items-center">
                            <h4 class="mb-0">Editar Área</h4>
                            <button type="button" class="btn-close" onclick="closeEditForm()"></button>
                        </div>

                        <form id="editAreaForm" class="needs-validation" novalidate>
                            <input type="hidden" name="id" id="edit_id">

                            <div class="mb-3">
                                <label class="form-label">Nome da Área</label>
                                <input type="text" name="nome" id="edit_nome" class="form-control" required>
                                <div class="invalid-feedback">Informe um nome válido.</div>
                            </div>

                            <button type="submit" class="btn btn-success w-100">
                                Salvar Alterações
                            </button>
                        </form>

                        <div id="editMessage" class="mt-3"></div>

                    </div>
                </div>

            </div>
        </div>

    </main>

<script>
// Utilitário para evitar XSS ao inserir textos no HTML
function escapeHtml(text) {
    return text.replace(/&/g, "&amp;")
               .replace(/</g, "&lt;")
               .replace(/>/g, "&gt;")
               .replace(/"/g, "&quot;")
               .replace(/'/g, "&#039;");
}

/* BUSCA */
function searchArea() {
    const input = document.getElementById("searchInput")?.value.toLowerCase() || "";
    const rows = document.querySelectorAll("#areaTable tbody tr");

    rows.forEach(row => {
        const id = row.children[0].innerText.toLowerCase();
        const nome = row.children[1].innerText.toLowerCase();
        row.style.display = (id.includes(input) || nome.includes(input)) ? "" : "none";
    });
}

/* MOSTRAR FORM DE EDIÇÃO */
function showEditForm(id, nome) {
    const formContainer = document.getElementById("editFormContainer");
    const fieldId = document.getElementById("edit_id");
    const fieldNome = document.getElementById("edit_nome");

    if (!formContainer || !fieldId || !fieldNome) {
        console.error("Formulário de edição não encontrado.");
        return;
    }

    fieldId.value = id;
    fieldNome.value = nome;

    formContainer.style.display = "block";
}

/* FECHAR FORM DE EDIÇÃO */
function closeEditForm() {
    const container = document.getElementById("editFormContainer");
    const msg = document.getElementById("editMessage");

    if (container) container.style.display = "none";
    if (msg) msg.innerHTML = "";
}

/* SUBMIT UPDATE */
const editForm = document.getElementById("editAreaForm");

if (editForm) {
    editForm.addEventListener("submit", function (e) {
        e.preventDefault();

        if (!editForm.checkValidity()) {
            editForm.classList.add("was-validated");
            return;
        }

        const formData = new FormData(editForm);

        fetch("../apis/set_area.php", {
            method: "POST",
            body: formData
        })
        .then(res => res.text())
        .then(data => {
            const msg = document.getElementById("editMessage");
            if (!msg) return;

            if (data.toLowerCase().includes("sucesso")) {
                msg.innerHTML = `<div class="alert alert-success">Área atualizada com sucesso!</div>`;
                setTimeout(() => location.reload(), 2000); // <-- 3 segundos
            } else {
                msg.innerHTML = `<div class="alert alert-danger">Erro: ${escapeHtml(data)}</div>`;
            }
        })
        .catch(() => {
            const msg = document.getElementById("editMessage");
            if (msg) msg.innerHTML = `<div class="alert alert-danger">Erro ao atualizar área.</div>`;
        });
    });
} else {
    console.warn("editAreaForm não encontrado na página.");
}

//* DELETE */
function deleteArea(id) {
    if (!confirm("Deseja realmente excluir esta área?")) return;

    const formData = new FormData();
    formData.append("id", id);

    fetch("../apis/deletar_area.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.text())
    .then(data => {
        const msg = document.getElementById("message");

        if (!msg) return;

        if (data.toLowerCase().includes("sucesso")) {
            msg.innerHTML = `
                <div class="alert alert-success">
                    ${data}
                </div>`;
        } else {
            msg.innerHTML = `
                <div class="alert alert-danger">
                    ${escapeHtml(data)}
                </div>`;
        }

        // Exibir mensagem por mais tempo (3 segundos)
        setTimeout(() => location.reload(), 2000);
    })
    .catch(() => {
        const msg = document.getElementById("message");
        if (msg)
            msg.innerHTML = `<div class="alert alert-danger">Erro ao excluir área.</div>`;
    });
}

</script>


</body>
</html>
