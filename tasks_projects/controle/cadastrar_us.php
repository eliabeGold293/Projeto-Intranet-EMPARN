<?php
require_once __DIR__ . '/../config/connection.php';

// Carrega classes
try {
    $stmt = $pdo->prepare("SELECT id, nome FROM classe_usuario ORDER BY nome ASC");
    $stmt->execute();
    $classe_usuario = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro ao carregar classes: " . $e->getMessage());
}

// Carrega áreas
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
            background-color: #eef1f4;
            display: flex;
            margin: 0;
        }

        .main-content {
            flex: 1;
            padding: 35px;
            margin-left: 250px;
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 20px;
            }
        }

        /* Título principal igual ao Painel de Controle */
        .page-title {
            font-size: 2rem;
            font-weight: 700;
            color: #0046a0;
            margin-bottom: 25px;
        }

        /* Container moderno */
        .card-modern {
            background: #fff;
            border-radius: 10px;
            padding: 25px;
            border: none;
            box-shadow: 0 4px 14px rgba(0,0,0,0.08);
        }

        .form-label {
            font-weight: 600;
            color: #003b87;
        }

        .btn-success {
            padding: 10px 20px;
            font-size: 1rem;
            font-weight: 600;
        }

        /* Caixa de mensagens */
        #message .alert {
            border-radius: 8px;
        }
    </style>
</head>

<body>

    <?php include __DIR__ . '/../templates/gen_menu.php'; ?>

    <main class="main-content">

        <!-- Título fora da caixa - estilo painel -->
        <h2 class="page-title">
            <i class="bi bi-person-plus"></i> Cadastro de Usuário
        </h2>

        <div class="card-modern">

            <form id="userForm" class="needs-validation" novalidate>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nome</label>
                        <input type="text" name="nome" class="form-control" required>
                        <div class="invalid-feedback">Informe o nome.</div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                        <div class="invalid-feedback">Informe um email válido.</div>
                    </div>
                </div>

                <div class="row">

                    <!-- REMOVIDO O CAMPO SENHA -->

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Classe</label>
                        <select name="classe_id" class="form-select" required>
                            <option value="" disabled selected>Selecione...</option>
                            <?php foreach ($classe_usuario as $class_us): ?>
                                <option value="<?= $class_us['id'] ?>"><?= htmlspecialchars($class_us['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Selecione uma classe.</div>
                    </div>

                </div>

                <div class="mb-4">
                    <label class="form-label">Área</label>
                    <select name="area_id" class="form-select" required>
                        <option value="" disabled selected>Selecione...</option>
                        <?php foreach ($areas as $area): ?>
                            <option value="<?= $area['id'] ?>"><?= htmlspecialchars($area['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback">Selecione uma área.</div>
                </div>

                <button type="submit" class="btn btn-success">
                    <i class="bi bi-check2-circle"></i> Salvar
                </button>

            </form>

            <div id="message" class="mt-4"></div>

        </div>

    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Validação Bootstrap
        (function () {
            'use strict';
            const forms = document.querySelectorAll('.needs-validation');
            forms.forEach(form => {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        })();

        document.getElementById("userForm").addEventListener("submit", function(e) {
            e.preventDefault();

            if (!this.checkValidity()) return;

            const formData = new FormData(this);

            fetch("../apis/criar_us.php", {
                method: "POST",
                body: formData
            })
            .then(async res => {
                const raw = await res.text(); // pega a resposta crua

                console.log("=== RESPOSTA CRUA DA API ===");
                console.log(raw); // MOSTRA O QUE EU PRECISO ANALISAR

                let json;

                try {
                    json = JSON.parse(raw);
                } catch (e) {
                    // JSON inválido → mostrar erro completo
                    document.getElementById("message").innerHTML =
                        `<div class="alert alert-danger">
                            <strong>Erro inesperado:</strong><br>
                            A API retornou um conteúdo inválido:<br><br>
                            <pre>${raw}</pre>
                        </div>`;
                    throw e; // interrompe aqui
                }

                return json;
            })
            .then(json => {
                const msgDiv = document.getElementById("message");

                if (json.success) {
                    msgDiv.innerHTML =
                        `<div class="alert alert-success">
                            <i class="bi bi-check-circle"></i> ${json.message}
                        </div>`;

                    this.reset();
                    this.classList.remove("was-validated");

                } else {
                    msgDiv.innerHTML =
                        `<div class="alert alert-danger">
                            <i class="bi bi-x-circle"></i> ${json.message}<br>
                            <small>${json.error ?? ""}</small>
                        </div>`;
                }
            })
            .catch((err) => {
                document.getElementById("message").innerHTML =
                    `<div class="alert alert-danger">
                        <i class="bi bi-x-circle"></i> Erro inesperado: ${err}
                    </div>`;
            });

        });


    </script>
</body>
</html>
