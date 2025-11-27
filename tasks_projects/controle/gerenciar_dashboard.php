<?php 
require_once "../config/connection.php";
include '../templates/gen_menu.php';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Dashboard - EMPARN</title>
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">

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

        h2, h3 {
            font-size: 1.8rem;
            font-weight: bold;
            color: #0046a0;
            margin-bottom: 20px;
        }

        .card-box {
            background: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        footer {
            margin-left: 250px;
            background: #e9ecef;
            padding: 15px;
            text-align: center;
            border-top: 1px solid #ccc;
            margin-top: 40px;
        }
    </style>
</head>
<body>
    <!-- Menu lateral -->
    <?php include '../templates/gen_menu.php'; ?>

    <!-- Conteúdo principal -->
    <main class="main-content">
        <h2><i class="bi bi-bar-chart"></i> Gerenciar Dashboard</h2>

        <!-- Formulário de criação de card -->
        <div class="card-box">
            <h5 class="text-success"><i class="bi bi-plus-circle"></i> Adicionar Novo Card</h5>
            <form action="../apis/salvar_cards.php" method="POST" class="needs-validation" novalidate>
                <div class="mb-3">
                    <label class="form-label">Nome do Card:</label>
                    <input type="text" class="form-control" name="titulo" required>
                    <div class="invalid-feedback">Informe o nome do card.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Cor de Fundo:</label><br>
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

        <!-- Listagem dos cards -->
        <div class="card-box">
            <h5 class="text-primary"><i class="bi bi-collection"></i> Cards Atuais</h5>
            <table class="table table-striped table-bordered shadow-sm">
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
                            <a href="../apis/deletar_card.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Deseja realmente excluir este card?');">Excluir</a>
                        </td>
                    </tr>
                    <?php
                        endforeach;
                    else:
                    ?>
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
    // Validação Bootstrap
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
</body>
</html>
