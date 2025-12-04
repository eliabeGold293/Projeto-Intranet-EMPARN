<?php
session_start();

// Impede o navegador de armazenar a página em cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Se não estiver logado → volta ao login
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['grau_acesso'])) {
    header("Location: login.php");
    exit;
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Interno</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f5f7fa;
            font-family: 'Segoe UI', Arial, sans-serif;
        }

        .card-panel {
            max-width: 850px;
            margin: 60px auto;
            padding: 30px;
            border-radius: 12px;
            background: white;
            box-shadow: 0 6px 20px rgba(0,0,0,0.08);
        }

        .titulo {
            font-size: 2rem;
            font-weight: 700;
            color: #003b82;
        }

        .subtitulo {
            font-size: 1.1rem;
            color: #555;
        }

        .btn-custom {
            padding: 10px 22px;
            font-size: 16px;
            border-radius: 8px;
        }
    </style>
</head>
<body>

<div class="card-panel">
    <h1 class="titulo mb-3">Bem-vindo!</h1>
    <p class="subtitulo mb-4">
        Você está autenticado no sistema. Esta é uma página interna protegida.
    </p>

    <div class="alert alert-primary">
        <strong>Usuário ID:</strong> <?= $_SESSION['usuario_id']; ?><br>
        <strong>Grau de acesso:</strong> <?= $_SESSION['grau_acesso']; ?>
    </div>

    <div class="d-flex gap-3">
        <a href="home.php" class="btn btn-primary btn-custom">Ir para o Painel</a>
        <a href="logout.php" class="btn btn-outline-danger btn-custom">Sair</a>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>