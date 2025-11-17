<?php
require_once "../config/connection.php";
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EMPARN - Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./static/index.css">
</head>
<body>

    <!-- Cabeçalho -->
    <header class="text-center py-3 bg-light shadow-sm">
        <img src="img/logo-emparn.png" alt="Logo EMPARN" height="100">
        <nav class="mt-2">
            <a href="../controle/index_controle.php" class="btn btn-outline-primary btn-sm">Controle</a>
        </nav>
    </header>

    <!-- Carrossel -->
    <?php
    $sql_carrossel = "SELECT * FROM noticias ORDER BY data_publicacao DESC LIMIT 3";
    $stmt_carrossel = $pdo->query($sql_carrossel);
    $result_carrossel = $stmt_carrossel->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <div id="noticiasCarrossel" class="carousel slide mt-4" data-bs-ride="carousel">
        <div class="carousel-inner">
            <?php
            $ativo = true;
            if (count($result_carrossel) > 0):
                foreach ($result_carrossel as $row):
            ?>
            <div class="carousel-item <?= $ativo ? 'active' : '' ?>">
                <img src="../uploads_noticias/<?= htmlspecialchars($row['imagem']) ?>" class="d-block w-100" style="height: 400px; object-fit: cover;" alt="Notícia">
                <div class="carousel-caption d-none d-md-block bg-dark bg-opacity-50 rounded">
                    <h5><?= htmlspecialchars($row['titulo']) ?></h5>
                    <p><?= htmlspecialchars($row['subtitulo']) ?></p>
                </div>
            </div>
            <?php $ativo = false; endforeach; endif; ?>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#noticiasCarrossel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#noticiasCarrossel" data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
        </button>
    </div>

    <!-- Dashboard Dinâmico -->
    <section class="container mt-5">
        <h2 class="text-center mb-4">Painel de Serviços</h2>
        <div class="row row-cols-1 row-cols-md-4 g-4">
            <?php
            $sql_cards = "SELECT * FROM dashboard ORDER BY id DESC";
            $stmt_cards = $pdo->query($sql_cards);
            $result_cards = $stmt_cards->fetchAll(PDO::FETCH_ASSOC);

            if (count($result_cards) > 0):
                foreach ($result_cards as $card):
            ?>
            <div class="col">
                <a href="<?= htmlspecialchars($card['link']) ?>" target="_blank" style="text-decoration:none;">
                    <div class="card h-100 text-center shadow-sm" style="background:<?= htmlspecialchars($card['cor']) ?>; color:white;">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($card['titulo']) ?></h5>
                        </div>
                    </div>
                </a>
            </div>
            <?php endforeach; else: ?>
            <p class="text-center text-muted">Nenhum quadradinho cadastrado ainda.</p>
            <?php endif; ?>
        </div>
    </section>

    <!-- Histórico de Notícias -->
    <?php
    $sql_historico = "SELECT * FROM noticias ORDER BY data_publicacao DESC LIMIT 6 OFFSET 3";
    $stmt_historico = $pdo->query($sql_historico);
    $result_historico = $stmt_historico->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <section class="container mt-5 mb-5">
        <h2 class="text-center mb-4">Notícias Anteriores</h2>
        <div class="row row-cols-1 row-cols-md-3 g-4">
            <?php foreach ($result_historico as $row): ?>
            <div class="col">
                <div class="card h-100 shadow-sm">
                    <img src="../uploads_noticias/<?= htmlspecialchars($row['imagem']) ?>" class="card-img-top" alt="<?= htmlspecialchars($row['titulo']) ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($row['titulo']) ?></h5>
                        <p class="card-text"><?= htmlspecialchars($row['subtitulo']) ?></p>
                        <small class="text-muted">Publicado em <?= date('d/m/Y', strtotime($row['data_publicacao'])) ?></small>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <footer class="text-center bg-dark text-white py-3">
        <p>© <?= date('Y') ?> EMPARN - Todos os direitos reservados.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
