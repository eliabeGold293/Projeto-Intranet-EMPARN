<?php
require_once "../config/connection.php";
include '../templates/gen_menu.php';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Notícias - EMPARN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background-color: #f4f6f8;
            margin: 0;
        }

        .main-content {
            margin-left: 250px; /* espaço para o menu lateral */
            padding: 30px;
        }

        h2 {
            font-size: 1.8rem;
            font-weight: bold;
            color: #198754; /* verde bootstrap */
            margin-bottom: 20px;
        }

        form {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            max-width: 800px;
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

<div class="main-content">
    <h2>📰 Cadastro de Notícias</h2>

    <?php if (isset($_GET['msg']) && $_GET['msg'] == 'ok'): ?>
        <div class="alert alert-success">Notícia cadastrada com sucesso!</div>
    <?php endif; ?>

    <form action="../apis/salvar_noticia.php" method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label class="form-label">Título:</label>
            <input type="text" name="titulo" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Subtítulo:</label>
            <input type="text" name="subtitulo" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Texto da Notícia:</label>
            <textarea name="texto" class="form-control" rows="6" required></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Autoria:</label>
            <input type="text" name="autoria" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Link da Notícia:</label>
            <input type="url" name="link" class="form-control" placeholder="https://www.exemplo.com" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Imagem da Notícia:</label>
            <input type="file" name="imagem" class="form-control" accept="image/*" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Fonte da imagem:</label>
            <textarea name="fonte_imagem" class="form-control" required></textarea>
        </div>

        <button type="submit" class="btn btn-success px-4">Salvar Notícia</button>
    </form>
</div>

<footer>
    <small>© <?= date('Y') ?> EMPARN - Painel de Controle</small>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
