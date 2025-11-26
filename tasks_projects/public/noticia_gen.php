<?php
require_once "../config/connection.php";

$id = $_GET['id'] ?? null;
if (!$id) {
    die("Notícia não encontrada.");
}

// Buscar notícia principal
$stmt = $pdo->prepare("SELECT * FROM noticias WHERE id = ?");
$stmt->execute([$id]);
$noticia = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$noticia) {
    die("Notícia não encontrada.");
}

// Buscar tópicos relacionados
$stmtTopicos = $pdo->prepare("SELECT * FROM noticia_topicos WHERE noticia_id = ? ORDER BY ordem ASC");
$stmtTopicos->execute([$id]);
$topicos = $stmtTopicos->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($noticia['titulo']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { 
            font-family: 'Segoe UI', Arial, sans-serif; 
            background: #f8f9fa; 
            margin: 0;
        }
        header {
            width: 100%;
            background-color: #fff;
            border-bottom: 1px solid #ccc;
            padding: 15px 20px;
            margin-bottom: 20px;
        }
        .container { max-width: 900px; }
        h1 { 
            color: #198754; 
            margin-bottom: 10px; 
            text-align: justify; /* título justificado */
        }
        h4 { 
            text-align: justify; /* subtítulo justificado */
        }
        h3 { 
            color: #0d6efd; 
            margin-top: 25px; 
        }
        img { 
            max-height: 400px; 
            object-fit: cover; 
            border-radius: 6px; 
        }
        .topico { margin-bottom: 40px; }
        .meta { font-size: 0.9rem; color: #6c757d; }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../templates/header.php'; ?>

    <div class="container py-5">
        <!-- Cabeçalho da notícia -->
        <h1><?= htmlspecialchars($noticia['titulo']) ?></h1>
        <?php if ($noticia['subtitulo']): ?>
            <h4 class="text-muted"><?= htmlspecialchars($noticia['subtitulo']) ?></h4>
        <?php endif; ?>
        <p class="meta">
            <strong>Autoria:</strong> <?= htmlspecialchars($noticia['autoria']) ?> |
            <strong>Publicado em:</strong> <?= date('d/m/Y H:i', strtotime($noticia['data_publicacao'])) ?>
        </p>
        <hr>

        <!-- Renderização dos tópicos -->
        <?php foreach ($topicos as $t): ?>
            <div class="topico">
                <?php if ($t['titulo_topico']): ?>
                    <h3><?= htmlspecialchars($t['titulo_topico']) ?></h3>
                <?php endif; ?>

                <?php if ($t['imagem_topico']): ?>
                    <img src="../<?= htmlspecialchars($t['imagem_topico']) ?>" class="img-fluid my-3" alt="Imagem do tópico">
                    <?php if ($t['fonte_imagem']): ?>
                        <p class="meta"><strong>Fonte da imagem:</strong> <?= htmlspecialchars($t['fonte_imagem']) ?></p>
                    <?php endif; ?>
                <?php endif; ?>

                <?php
                // Quebra o texto em parágrafos usando a quebra de linha
                $paragrafos = preg_split('/\r\n|\r|\n/', $t['texto_topico']);

                foreach ($paragrafos as $p) {
                    $p = trim($p);
                    if ($p !== '') {
                        echo '<p style="text-align: justify;">' . htmlspecialchars($p) . '</p>';
                    }
                }
                ?>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
