<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Criar Classe</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background-color: #f4f6f8;
            margin: 0;
            display: flex;
        }

        .main-content {
            flex: 1;
            padding: 25px 30px;
            margin-left: 250px;
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 20px;
            }
        }

        .page-title {
            font-size: 28px;
            font-weight: 700;
            color: #0046a0;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.10);
        }

        .form-control {
            border-radius: 8px;
            height: 45px;
        }

        .btn-success {
            border-radius: 8px;
            padding: 12px;
            font-size: 16px;
        }

        .loading-spinner {
            display: none;
            margin-left: 8px;
        }
    </style>
</head>

<body>

    <!-- Menu lateral -->
    <<?php include __DIR__ . '/../templates/gen_menu.php'; ?>

    <!-- Conteúdo principal -->
    <main class="main-content">

        <h2 class="page-title">
            <i class="bi bi-diagram-3-fill"></i>
            Criar Classe
        </h2>

        <!-- Caixa agora fica logo abaixo do título e alinhada à esquerda -->
        <div class="col-lg-6 col-md-8 col-sm-12 p-0">

            <div class="card">
                <div class="card-body p-4">

                    <form id="createClassForm" class="needs-validation" novalidate>

                        <div class="mb-3">
                            <label for="nome" class="form-label fw-semibold">Nome da Classe</label>
                            <input type="text" name="nome" id="nome" class="form-control" placeholder="Ex: Administrador" required>
                            <div class="invalid-feedback">Informe o nome da classe.</div>
                        </div>

                        <div class="mb-3">
                            <label for="grau" class="form-label fw-semibold">Grau de Acesso (1 a 4)</label>
                            <input type="number" name="grau_acesso" id="grau" class="form-control" min="1" max="4" placeholder="Ex: 3" required>
                            <div class="invalid-feedback">Informe um grau de acesso entre 1 e 4.</div>
                        </div>

                        <button type="submit" class="btn btn-success w-100 d-flex justify-content-center align-items-center">
                            <span>Salvar Classe</span>
                            <div class="spinner-border spinner-border-sm loading-spinner" role="status"></div>
                        </button>

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
            "use strict";
            const forms = document.querySelectorAll(".needs-validation");

            forms.forEach(form => {
                form.addEventListener("submit", event => {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add("was-validated");
                }, false);
            });
        })();

        // Submissão AJAX moderna
        const form = document.getElementById("createClassForm");
        const messageDiv = document.getElementById("message");
        const spinner = document.querySelector(".loading-spinner");

        form.addEventListener("submit", async function(e) {
            e.preventDefault();
            if (!form.checkValidity()) return;

            spinner.style.display = "inline-block";

            const formData = new FormData(form);

            try {
                const response = await fetch("criar-classe-usuario-api", {
                    method: "POST",
                    body: formData
                });

                const text = await response.text();
                let isSuccess = text.toLowerCase().includes("sucesso");

                messageDiv.innerHTML = `
                    <div class="alert alert-${isSuccess ? "success" : "danger"}">
                        ${isSuccess ? "Classe criada com sucesso!" : "Erro ao criar classe: " + text}
                    </div>
                `;

                if (isSuccess) {
                    form.reset();
                    form.classList.remove("was-validated");
                }

                setTimeout(() => (messageDiv.innerHTML = ""), 3500);

            } catch (error) {
                messageDiv.innerHTML = `
                    <div class="alert alert-danger">Erro inesperado ao criar classe.</div>
                `;
            } finally {
                spinner.style.display = "none";
            }
        });
    </script>

</body>
</html>
