<?php
// historico_noticias.php
require_once "../config/connection.php";

$sql_historico = "SELECT * FROM noticias ORDER BY data_publicacao DESC LIMIT 6 OFFSET 3";
$stmt_historico = $pdo->query($sql_historico);
$result_historico = $stmt_historico->fetchAll(PDO::FETCH_ASSOC);
?>

<section class="container mt-5 mb-5 historico-noticias">
    <h2 class="text-center mb-4">Notícias Anteriores</h2>
    <div class="row row-cols-1 row-cols-md-3 g-4">
        <?php foreach ($result_historico as $row): ?>
        <div class="col">
            <a href="<?= htmlspecialchars($row['link']) ?>" target="_blank" style="text-decoration:none; color:inherit;">
                <div class="card h-100 shadow-sm">
                    <img src="/tasks_projects/<?= htmlspecialchars($row['imagem']) ?>" 
                         class="card-img-top" 
                         alt="<?= htmlspecialchars($row['titulo']) ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($row['titulo']) ?></h5>
                        <p class="card-text"><?= htmlspecialchars($row['subtitulo']) ?></p>
                        <small class="text-muted">
                            Publicado em <?= date('d/m/Y', strtotime($row['data_publicacao'])) ?>
                        </small>
                    </div>
                </div>
            </a>
        </div>
        <?php endforeach; ?>
    </div>
</section>
