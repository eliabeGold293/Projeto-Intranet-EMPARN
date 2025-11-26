<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Criar Classe</title>
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
                    <h4 class="mb-0">Criar Classe</h4>
                </div>
                <div class="card-body">
                    <form id="createClassForm" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label class="form-label">Nome da Classe</label>
                            <input type="text" name="nome" class="form-control" required>
                            <div class="invalid-feedback">Informe o nome da classe.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Grau de Acesso (1 a 4)</label>
                            <input type="number" name="grau_acesso" class="form-control" min="1" max="4" required>
                            <div class="invalid-feedback">Informe um grau de acesso entre 1 e 4.</div>
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
        document.getElementById("createClassForm").addEventListener("submit", function(e) {
            e.preventDefault();

            if (!this.checkValidity()) return;

            const formData = new FormData(this);

            fetch("../apis/criar_classe_us.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                const msgDiv = document.getElementById("message");
                if (data.toLowerCase().includes("sucesso")) {
                    msgDiv.innerHTML = '<div class="alert alert-success">Classe criada com sucesso!</div>';
                    this.reset();
                    this.classList.remove('was-validated');
                } else {
                    msgDiv.innerHTML = '<div class="alert alert-danger">Erro ao criar classe: ' + data + '</div>';
                }
            })
            .catch(() => {
                document.getElementById("message").innerHTML = '<div class="alert alert-danger">Erro ao criar classe.</div>';
            });
        });
    </script>
</body>
</html>
