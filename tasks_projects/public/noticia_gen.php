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
    .meta { font-size: 0.9rem; color: #6c757d; margin-bottom: 1rem; }

    /* ====== WRAPPER COM FUNDO BORRADO ====== */
    .smart-img-wrapper {
        position: relative;
        width: 100%;
        max-width: 1100px;
        margin: 0 auto;
        border-radius: .75rem;
        overflow: hidden;
        aspect-ratio: 16 / 9; /* atualizado via JS */
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Fundo borrado */
    .smart-img-wrapper::before {
        content: "";
        position: absolute;
        inset: 0;
        background-size: cover;
        background-position: center;
        filter: blur(25px) brightness(0.8);
        transform: scale(1.2);
        z-index: 1;
    }

    /* Imagem nítida */
    .smart-img-wrapper img {
        position: relative;
        z-index: 2;
        width: auto;
        height: auto;
        max-width: 100%;
        max-height: 600px;
        object-fit: contain; /* atualizado via JS */
        transition: object-fit .25s ease;
        display: block;
    }

    @media (max-width: 768px) {
        .smart-img-wrapper img {
            max-height: 400px;
        }
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
                <div class="smart-img-wrapper mb-2" style="--img:url('../<?= htmlspecialchars($noticia['imagem']) ?>')">
                    <img src="../<?= htmlspecialchars($noticia['imagem']) ?>" class="imagem-principal">
                </div>

                <?php if ($noticia['fonte_imagem']): ?>
                    <p class="fonte-imagem">
                        <i class="bi bi-camera"></i> Fonte: <?= htmlspecialchars($noticia['fonte_imagem']) ?>
                    </p>
                <?php endif; ?>
            <?php endif; ?>

            <div class="mt-4" style="font-size: 1.1rem;">
                <?= $noticia['texto'] ?>
            </div>

        </div>
    </div>

    <?php foreach ($topicos as $t): ?>
        <div class="card shadow-sm card-noticia mb-4">
            <div class="card-body">

                <?php if ($t['titulo']): ?>
                    <h3 class="text-primary"><?= htmlspecialchars($t['titulo']) ?></h3>
                <?php endif; ?>

                <?php if ($t['imagem']): ?>
                    <div class="smart-img-wrapper mb-2" style="--img:url('../<?= htmlspecialchars($t['imagem']) ?>')">
                        <img src="../<?= htmlspecialchars($t['imagem']) ?>" class="imagem-topico">
                    </div>

                    <?php if ($t['fonte_imagem']): ?>
                        <p class="fonte-imagem">
                            <i class="bi bi-camera"></i> Fonte: <?= htmlspecialchars($t['fonte_imagem']) ?>
                        </p>
                    <?php endif; ?>
                <?php endif; ?>

                <div class="mt-3">
                    <?= $t['texto'] ?>
                </div>

            </div>
        </div>
    <?php endforeach; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.querySelectorAll(".smart-img-wrapper").forEach(wrapper => {
    const img = wrapper.querySelector("img");

    img.addEventListener("load", () => {

        const w = img.naturalWidth;
        const h = img.naturalHeight;
        const ratio = w / h;

        wrapper.style.aspectRatio = ratio;
        wrapper.style.setProperty("--img", `url('${img.src}')`);

        if (ratio < 0.8) {
            img.style.objectFit = "contain";
        } else {
            img.style.objectFit = "cover";
        }
    });
});
</script>

</body>
</html>
