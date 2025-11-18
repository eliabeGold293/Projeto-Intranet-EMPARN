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
<style>
    .btn-gradient {
        background: linear-gradient(135deg, #007bff, #00c6ff);
        color: #fff;
        border: none;
        transition: all 0.3s ease;
    }

    .btn-gradient:hover {
        background: linear-gradient(135deg, #0056b3, #0096c7);
        transform: translateY(-2px);
        box-shadow: 0 8px 15px rgba(0,0,0,0.2);
    }
</style>
<body>
    <!--Cabeçalho -->
    <?php include __DIR__ . '/../templates/header_emparn.php'; ?>

    <!-- Carrossel de Notícias -->
    <?php include __DIR__ . '/../templates/carrossel_noticias.php'; ?>

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
            <p class="text-center text-muted">Nenhuma card cadastrado ainda.</p>
            <?php endif; ?>
        </div>
    </section>

    <!-- Histórico de Notícias -->
    <?php include __DIR__ . '/../templates/historico_noticias.php'; ?>

    <div class="container text-center mb-5">
        <a href="todas_as_noticias.php" 
        class="btn btn-gradient btn-lg px-4 py-2 shadow-lg rounded-pill">
            <i class="bi bi-newspaper"></i> Confira todas as notícias
        </a>
    </div>

    <footer class="text-center bg-dark text-white py-3">
        <p>© <?= date('Y') ?> EMPARN - Todos os direitos reservados.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
