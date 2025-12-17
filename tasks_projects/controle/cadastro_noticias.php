<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Notícias - EMPARN</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <!-- TinyMCE -->
    <script src="../tinymce_8.2.2/tinymce/js/tinymce/tinymce.min.js"></script>
    <script src="../tinymce_8.2.2/tinymce/js/tinymce/langs/pt_BR.js"></script>


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
            margin-left: 250px;
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 20px;
            }
        }

        .card {
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
    </style>
</head>

<body>

    <!-- Menu reutilizável -->
    <?php include __DIR__ . '/../templates/gen_menu.php'; ?>
    <?php require_once __DIR__ . '/../config/connection.php';?>

    <!-- Conteúdo principal -->
    <main class="main-content">
        <h2 class="mb-4 text-primary">📰 Cadastro de Notícias</h2>

        <button id="addNewsForm" class="btn btn-success mb-4 d-flex align-items-center gap-2 shadow-sm px-3">
            <i class="bi bi-plus-circle fs-5"></i>
            <span class="fw-semibold">Nova Notícia</span>
        </button>

        <!-- Container onde os formulários aparecem -->
        <div id="newsFormsContainer"></div>
    </main>

    <footer class="bg-light text-center py-3 mt-4">
        <small>© <?= date('Y') ?> EMPARN - Painel de Controle</small>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- JS separado -->
    <script src="../public/js/noticias.js"></script>

</body>
</html>
