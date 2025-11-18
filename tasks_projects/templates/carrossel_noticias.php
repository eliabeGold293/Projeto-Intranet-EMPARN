<?php
// carrossel_noticias.php
require_once "../config/connection.php";

$sql_carrossel = "SELECT * FROM noticias ORDER BY data_publicacao DESC LIMIT 3";
$stmt_carrossel = $pdo->query($sql_carrossel);
$result_carrossel = $stmt_carrossel->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
    #noticiasCarrossel .carousel-item {
        position: relative;
        height: 400px;
        overflow: hidden;
    }

    #noticiasCarrossel .carousel-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    #noticiasCarrossel .carousel-caption {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        padding: 20px;
        background-color: rgba(0, 0, 0, 0.5);
        color: white;
        border-radius: 0;
        text-align: left; /* texto alinhado à esquerda */
    }

    #noticiasCarrossel {
        border-radius: 10px; /* arredonda o carrossel */
        overflow: hidden;    /* garante que a imagem não ultrapasse as bordas */
    }

    #noticiasCarrossel .carousel-item img {
        border-radius: 15px; /* arredonda as imagens também */
    }

    /* remove sublinhado e mantém cor ao clicar */
    #noticiasCarrossel a {
        text-decoration: none;
        color: inherit;
    }
</style>

<div class="container">
    <div id="noticiasCarrossel" class="carousel slide mt-4" data-bs-ride="carousel">
        <div class="carousel-inner">
            <?php
            $ativo = true;
            foreach ($result_carrossel as $row):
            ?>
            <div class="carousel-item <?= $ativo ? 'active' : '' ?>">
                <a href="<?= htmlspecialchars($row['link']) ?>" target="_blank">
                    <img src="/tasks_projects/<?= htmlspecialchars($row['imagem']) ?>" 
                         class="d-block w-100" 
                         style="height: 400px; object-fit: cover;" 
                         alt="Notícia">
                    <div class="carousel-caption d-none d-md-block bg-dark bg-opacity-50 rounded">
                        <h5><?= htmlspecialchars($row['titulo']) ?></h5>
                        <p><?= htmlspecialchars($row['subtitulo']) ?></p>
                    </div>
                </a>
            </div>
            <?php $ativo = false; endforeach; ?>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#noticiasCarrossel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#noticiasCarrossel" data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
        </button>
    </div>
</div>

<script>
  document.addEventListener("DOMContentLoaded", function() {
    var myCarousel = document.querySelector('#noticiasCarrossel');
    var carousel = new bootstrap.Carousel(myCarousel, {
      interval: 3000, // tempo entre slides (3 segundos)
      ride: 'carousel'
    });
  });
</script>
