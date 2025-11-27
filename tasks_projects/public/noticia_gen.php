<?php
require_once "../config/connection.php";

$id = $_GET['id'] ?? null;
if (!$id) die("Notícia não encontrada.");

$stmt = $pdo->prepare("SELECT * FROM noticias WHERE id = ?");
$stmt->execute([$id]);
$noticia = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$noticia) die("Notícia não encontrada.");

$stmtTopicos = $pdo->prepare("SELECT * FROM noticia_topicos WHERE noticia_id = ? ORDER BY ordem ASC");
$stmtTopicos->execute([$id]);
$topicos = $stmtTopicos->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($noticia['titulo']) ?></title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>
    body { background-color: #f8f9fa; }

    .titulo-noticia { color: #198754; }
    .subtitulo-noticia { color: #6c757d; }

    .meta { 
        font-size: 0.9rem; 
        color: #6c757d; 
        margin-bottom: 1rem; 
    }

    .imagem-principal, 
    .imagem-topico {
        width: 100%;
        max-height: 420px;
        object-fit: cover;
        border-radius: .5rem;
    }

    .fonte-imagem {
        font-size: .85rem;
        color: #6c757d;
        margin-top: .2rem;
        margin-bottom: .8rem;
    }

    .card-noticia {
        border-radius: .75rem;
    }
</style>
</head>

<body>

<?php include __DIR__ . '/../templates/header_emparn.php'; ?>

<div class="container my-3">
    <button type="button" class="btn btn-outline-secondary" onclick="history.back()">
        <i class="bi bi-arrow-left"></i> Voltar
    </button>
</div>


<div class="container pb-5">

    <!-- =============================== -->
    <!-- CARD PRINCIPAL DA NOTÍCIA       -->
    <!-- =============================== -->
    <div class="card shadow-sm card-noticia mb-4">
        <div class="card-body">

            <h1 class="titulo-noticia"><?= htmlspecialchars($noticia['titulo']) ?></h1>

            <?php if ($noticia['subtitulo']): ?>
                <h4 class="subtitulo-noticia"><?= htmlspecialchars($noticia['subtitulo']) ?></h4>
            <?php endif; ?>

            <p class="meta">
                <i class="bi bi-person"></i> <strong><?= htmlspecialchars($noticia['autoria']) ?></strong> —
                <i class="bi bi-calendar"></i> <?= date('d/m/Y H:i', strtotime($noticia['data_publicacao'])) ?>
            </p>

            <?php if ($noticia['imagem']): ?>
                <img src="../<?= htmlspecialchars($noticia['imagem']) ?>" class="imagem-principal mb-2">

                <?php if ($noticia['fonte_imagem']): ?>
                    <p class="fonte-imagem">
                        <i class="bi bi-camera"></i> Fonte: <?= htmlspecialchars($noticia['fonte_imagem']) ?>
                    </p>
                <?php endif; ?>
            <?php endif; ?>

            <div class="mt-4" style="font-size: 1.1rem;">
                <?= $noticia['texto'] ?> <!-- TinyMCE -->
            </div>

        </div>
    </div>


    <!-- =============================== -->
    <!-- TÓPICOS                         -->
    <!-- =============================== -->
    <?php foreach ($topicos as $t): ?>
        <div class="card shadow-sm card-noticia mb-4">
            <div class="card-body">

                <?php if ($t['titulo']): ?>
                    <h3 class="text-primary"><?= htmlspecialchars($t['titulo']) ?></h3>
                <?php endif; ?>

                <?php if ($t['imagem']): ?>
                    <img src="../<?= htmlspecialchars($t['imagem']) ?>" class="imagem-topico mb-2">

                    <?php if ($t['fonte_imagem']): ?>
                        <p class="fonte-imagem">
                            <i class="bi bi-camera"></i> Fonte: <?= htmlspecialchars($t['fonte_imagem']) ?>
                        </p>
                    <?php endif; ?>
                <?php endif; ?>

                <div class="mt-3">
                    <?= $t['texto'] ?> <!-- TinyMCE HTML -->
                </div>

            </div>
        </div>
    <?php endforeach; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
