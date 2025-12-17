<?php 
require_once __DIR__ . '/../config/connection.php';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Dashboard - EMPARN</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">

    <style>
        body {
            background-color: #eef1f4;
            display: flex;
            margin: 0;
            font-family: 'Inter', 'Segoe UI', Arial, sans-serif;
            min-height: 100vh;
        }

        .main-content {
            flex: 1;
            padding: 32px;
            margin-left: 250px;
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 20px;
            }
        }

        h2 {
            font-size: 1.9rem;
            font-weight: 700;
            color: #0d3b66;
        }

        h5 {
            font-weight: 600;
        }

        .card-box {
            background: #ffffff;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 30px;
            border: 1px solid #e1e5e9;
            box-shadow: 0px 3px 8px rgba(0,0,0,0.06);
        }

        .table th {
            background: #d1f0d1 !important;
        }

        footer {
            margin-left: 250px;
            background: #e9ecef;
            padding: 16px;
            text-align: center;
            border-top: 1px solid #ccc;
        }

        @media (max-width: 768px) {
            footer {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>

<?php include __DIR__ . '/../templates/gen_menu.php'; ?>

<main class="main-content">
    <h2><i class="bi bi-bar-chart me-2"></i> Gerenciar Dashboard</h2>

    <div class="card-box">
        <h5 class="text-success"><i class="bi bi-plus-circle"></i> Adicionar Novo Card</h5>
        <form id="form-card" action="../apis/salvar_cards.php" method="POST" class="needs-validation" novalidate>
            <div class="mb-3">
                <label class="form-label">Nome do Card:</label>
                <input type="text" class="form-control" name="titulo" required>
                <div class="invalid-feedback">Informe o nome do card.</div>
            </div>

            <div class="mb-3">
                <label class="form-label">Cor de Fundo:</label>
                <input type="color" class="form-control form-control-color" name="cor" value="#006400" required>
                <div class="invalid-feedback">Selecione uma cor.</div>
            </div>

            <div class="mb-3">
                <label class="form-label">Link de Destino:</label>
                <input type="url" class="form-control" name="link" placeholder="https://www.exemplo.com" required>
                <div class="invalid-feedback">Informe um link válido.</div>
            </div>

            <button type="submit" class="btn btn-success">Adicionar Card</button>
        </form>
    </div>

    <div class="card-box">
        <h5 class="text-primary"><i class="bi bi-collection"></i> Cards Atuais</h5>
        <table class="table table-striped table-bordered shadow-sm">
            <thead>
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
                        <div style="width:30px; height:30px; background:<?= htmlspecialchars($row['cor']) ?>; border-radius:6px;"></div>
                    </td>
                    <td>
                        <a href="<?= htmlspecialchars($row['link']) ?>" target="_blank">
                            <?= htmlspecialchars($row['link']) ?>
                        </a>
                    </td>
                    <td>
                        <button class="btn btn-danger btn-sm" onclick="deletarCard(<?= $row['id'] ?>)">
                            Excluir
                        </button>
                    </td>
                </tr>
                <?php endforeach; else: ?>
                <tr>
                    <td colspan="4" class="text-center text-muted">Nenhum card cadastrado ainda.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<footer>
    <small>© <?= date('Y') ?> EMPARN - Painel de Controle</small>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(() => {
    'use strict';
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
})();
</script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const form = document.querySelector("#form-card");

    form.addEventListener("submit", async function (e) {
        e.preventDefault();

        if (!form.checkValidity()) {
            form.classList.add("was-validated");
            return;
        }

        const formData = new FormData(form);

        try {
            const response = await fetch("../apis/salvar_cards.php", {
                method: "POST",
                body: formData
            });

            const result = await response.json();

            if (result.status === "success") {
                alert("Card adicionado com sucesso!");
                location.reload();
            } else {
                alert("Erro: " + result.message);
            }

        } catch (error) {
            console.error("Erro no envio:", error);
            alert("Erro ao enviar os dados.");
        }
    });
});
</script>

<script>
function deletarCard(id) {
    if (!confirm("Deseja realmente excluir este card?")) return;

    fetch("../apis/deletar_card.php?id=" + id)
        .then(res => res.json())
        .then(result => {
            if (result.status === "success") {
                alert("Card excluído com sucesso!");
                location.reload();
            } else {
                alert("Erro: " + result.message);
            }
        })
        .catch(err => {
            console.error("Erro ao excluir:", err);
            alert("Erro ao excluir o card.");
        });
}
</script>

</body>
</html>