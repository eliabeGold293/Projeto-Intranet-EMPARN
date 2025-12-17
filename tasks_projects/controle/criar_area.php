<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Criar Área de Atuação</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

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

        .page-title {
            font-size: 2rem;
            font-weight: 700;
            color: #0046a0;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* CARD padrão do sistema */
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
            height: auto;
        }

        .btn-success {
            border-radius: 6px;
            padding: 12px;
            font-size: 16px;
        }
    </style>
</head>
<body>

    <?php include __DIR__ . '/../templates/gen_menu.php'; ?>

    <main class="main-content">

        <h2 class="page-title">
            <i class="bi bi-briefcase-fill"></i>
            Criar Área de Atuação
        </h2>

        <div class="col-lg-6 col-md-8 col-sm-12 p-0">

            <div class="card-modern">

                <div class="card-header-modern">
                    <h4 class="mb-0">Nova Área</h4>
                </div>

                <form id="createAreaForm" class="needs-validation" novalidate>

                    <div class="mb-3">
                        <label class="form-label">Nome da Área</label>
                        <input type="text" name="nome" class="form-control" required>
                        <div class="invalid-feedback">Informe o nome da área.</div>
                    </div>

                    <button type="submit" class="btn btn-success w-100">
                        Salvar
                    </button>

                </form>

                <div id="message" class="mt-3"></div>

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

        // AJAX
        document.getElementById("createAreaForm").addEventListener("submit", function(e) {
            e.preventDefault();
            if (!this.checkValidity()) return;

            const formData = new FormData(this);

            fetch("../apis/criar_area.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                const msgDiv = document.getElementById("message");

                if (data.toLowerCase().includes("sucesso")) {
                    msgDiv.innerHTML = `<div class="alert alert-success">Área criada com sucesso!</div>`;
                    this.reset();
                    this.classList.remove('was-validated');
                } else {
                    msgDiv.innerHTML = `<div class="alert alert-danger">Erro ao criar área: ${data}</div>`;
                }
            })
            .catch(() => {
                document.getElementById("message").innerHTML =
                    `<div class="alert alert-danger">Erro ao criar área.</div>`;
            });
        });
    </script>

</body>
</html>
