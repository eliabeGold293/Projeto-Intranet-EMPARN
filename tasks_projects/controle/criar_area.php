<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Criar Área de Atuação</title>
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
            margin-left: 250px; /* largura padrão do menu lateral */
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
    <!-- Menu reutilizável -->
    <?php include '../templates/gen_menu.php'; ?>

    <!-- Conteúdo principal -->
    <main class="main-content">
        <div class="container">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Criar Área de Atuação</h4>
                </div>
                <div class="card-body">
                    <form id="createAreaForm" action="../apis/criar_area.php" method="POST" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label class="form-label">Nome da Área</label>
                            <input type="text" name="nome" class="form-control" required>
                            <div class="invalid-feedback">Informe o nome da área.</div>
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
                    msgDiv.innerHTML = '<div class="alert alert-success">Área criada com sucesso!</div>';
                    this.reset();
                    this.classList.remove('was-validated');
                } else {
                    msgDiv.innerHTML = '<div class="alert alert-danger">Erro ao criar área: ' + data + '</div>';
                }
            })
            .catch(() => {
                document.getElementById("message").innerHTML = '<div class="alert alert-danger">Erro ao criar área.</div>';
            });
        });
    </script>
</body>
</html>
