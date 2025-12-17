<?php
require_once __DIR__ . '/../config/connection.php';

$search = isset($_GET['q']) ? trim($_GET['q']) : '';

if ($search) {
    $sql = "SELECT * FROM noticias 
            WHERE titulo LIKE :search OR subtitulo LIKE :search 
            ORDER BY data_publicacao DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['search' => "%$search%"]);
} else {
    $sql = "SELECT * FROM noticias ORDER BY data_publicacao DESC";
    $stmt = $pdo->query($sql);
}

$noticias = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Todas as Notícias</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background-color: #f4f6f8;
            display: flex;
            margin: 0;
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
            transition: transform .2s, box-shadow .2s;
        }
        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.12);
        }

        .card-img-top {
            height: 180px;
            object-fit: cover;
            background: #ddd;
        }
    </style>
</head>
<body>

<!-- MENU LATERAL -->
<?php include __DIR__ . '/../templates/gen_menu.php'; ?>

<!-- CONTEÚDO PRINCIPAL -->
<main class="main-content">
    <div class="container">

        <!-- BUSCA -->
        <form method="GET" class="d-flex justify-content-center mb-4">
            <input type="text" name="q"
                   class="form-control w-50 me-2"
                   placeholder="Buscar notícias..."
                   value="<?= htmlspecialchars($search) ?>">
            <button class="btn btn-primary">
                <i class="bi bi-search"></i> Buscar
            </button>
        </form>

        <h2 class="text-center text-primary mb-4">
            <i class="bi bi-newspaper"></i> Notícias Registradas
        </h2>

        <!-- GRID -->
        <div class="row row-cols-1 row-cols-md-3 g-4">

            <?php if ($noticias): ?>
                <?php foreach ($noticias as $n): ?>
                    <div class="col">
                        <div class="card h-100">

                            <a href="noticia-gen?id=<?=$n['id']?>" style="text-decoration:none; color:inherit;">
                                <?php if ($n['imagem']): ?>
                                    <img src="/tasks_projects/uploads/<?= htmlspecialchars($n['imagem']) ?>" class="card-img-top">
                                <?php else: ?>
                                    <img src="https://via.placeholder.com/600x400?text=Sem+Imagem" class="card-img-top">
                                <?php endif; ?>
                            </a>

                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($n['titulo']) ?></h5>
                                <p class="text-muted"><?= htmlspecialchars($n['subtitulo']) ?></p>

                                <small class="text-secondary d-block mb-1">
                                    <i class="bi bi-calendar-event"></i>
                                    Publicado: <?= date("d/m/Y H:i", strtotime($n['data_publicacao'])) ?>
                                </small>

                                <small class="text-secondary d-block">
                                    <i class="bi bi-pencil-square"></i>
                                    Editado: <?= date("d/m/Y H:i", strtotime($n['data_edicao'])) ?>
                                </small>
                            </div>

                            <div class="card-footer d-flex justify-content-between">
                                <a href="editar-noticia?id=<?= $n['id'] ?>" class="btn btn-warning btn-sm">
                                    <i class="bi bi-pencil"></i> Editar
                                </a>

                                <a class="btn btn-danger btn-sm"
                                onclick="deleteNoticia(<?= $n['id'] ?>)"
                                href="javascript:void(0)">
                                    <i class="bi bi-trash"></i> Deletar
                                </a>

                            </div>

                        </div>
                    </div>
                <?php endforeach; ?>

            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        Nenhuma notícia encontrada.
                    </div>
                </div>
            <?php endif; ?>

        </div>
        <?php if (isset($_GET['deleted']) && $_GET['deleted'] == 1): ?>
            <div class="alert alert-success text-center">
                Notícia deletada com sucesso!
            </div>
        <?php endif; ?>

    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function deleteNoticia(id) {

    if (!confirm("Tem certeza que deseja deletar esta notícia?")) {
        return;
    }

    fetch("deletar-noticia?id=" + id)
        .then(response => response.json())
        .then(data => {
            if (data.status === "success") {
                // Atualiza a página mostrando a mensagem de sucesso
                window.location.href = "?deleted=1";
            } else {
                alert("Erro ao deletar: " + data.message);
            }
        })
        .catch(err => {
            console.error(err);
            alert("Erro ao se comunicar com o servidor.");
        });
}
</script>
</body>
</html>
