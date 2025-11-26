<?php
require_once "../config/connection.php";

// Busca todas as classes da tabela classe_usuario
try {
    $stmt = $pdo->prepare("SELECT id, nome, grau_acesso FROM classe_usuario ORDER BY nome ASC");
    $stmt->execute();
    $classe_usuario = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro ao carregar classes: " . $e->getMessage());
}

// Busca todas as áreas da tabela area_atuacao
try {
    $stmt = $pdo->prepare("SELECT id, nome FROM area_atuacao ORDER BY nome ASC");
    $stmt->execute();
    $areas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro ao carregar áreas: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastro de Usuário</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f4f6f8;
            display: flex;
            margin: 0;
        }

        .main-content {
            flex: 1;
            padding: 30px;
            margin-left: 250px; /* largura padrão do menu */
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
    </style>
</head>
<body>
    <?php include '../templates/gen_menu.php'; ?>

    <main class="main-content">
        <div class="container">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Cadastro de Usuário</h4>
                </div>
                <div class="card-body">
                    <form id="userForm" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label class="form-label">Nome</label>
                            <input type="text" name="nome" class="form-control" required>
                            <div class="invalid-feedback">Informe o nome.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                            <div class="invalid-feedback">Informe um email válido.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Senha</label>
                            <input type="password" name="senha" class="form-control" required>
                            <div class="invalid-feedback">Informe uma senha.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Classe</label>
                            <select name="classe_id" id="classe_id" class="form-select" required>
                                <?php foreach ($classe_usuario as $class_us): ?>
                                    <option value="<?= htmlspecialchars($class_us['id']) ?>">
                                        <?= htmlspecialchars($class_us['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Selecione uma classe.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Área</label>
                            <select name="area_id" id="area_id" class="form-select" required>
                                <?php foreach ($areas as $area): ?>
                                    <option value="<?= htmlspecialchars($area['id']) ?>">
                                        <?= htmlspecialchars($area['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Selecione uma área.</div>
                        </div>

                        <button type="submit" class="btn btn-success">Salvar</button>
                    </form>

                    <div id="message" class="mt-3"></div>
                </div>
            </div>
        </div>
    </main>

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

        // Submissão AJAX
        document.getElementById("userForm").addEventListener("submit", function(e) {
            e.preventDefault();

            if (!this.checkValidity()) return;

            const formData = new FormData(this);

            fetch("../apis/criar_us.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                const msgDiv = document.getElementById("message");
                if (data.toLowerCase().includes("sucesso")) {
                    msgDiv.innerHTML = '<div class="alert alert-success">Usuário criado com sucesso!</div>';
                    this.reset();
                    this.classList.remove('was-validated');
                } else {
                    msgDiv.innerHTML = '<div class="alert alert-danger">Erro ao criar usuário: ' + data + '</div>';
                }
            })
            .catch(() => {
                document.getElementById("message").innerHTML = '<div class="alert alert-danger">Erro ao criar usuário.</div>';
            });
        });
    </script>
</body>
</html>
