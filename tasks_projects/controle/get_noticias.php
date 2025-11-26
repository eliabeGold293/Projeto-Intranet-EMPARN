<?php
require_once "../config/connection.php";

$search = isset($_GET['q']) ? trim($_GET['q']) : '';

if ($search) {
    $sql_todas = "SELECT * FROM noticias 
                  WHERE titulo LIKE :search OR subtitulo LIKE :search 
                  ORDER BY data_publicacao DESC";
    $stmt_todas = $pdo->prepare($sql_todas);
    $stmt_todas->execute(['search' => "%$search%"]);
} else {
    $sql_todas = "SELECT * FROM noticias ORDER BY data_publicacao DESC";
    $stmt_todas = $pdo->query($sql_todas);
}
$result_todas = $stmt_todas->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Todas as Notícias - EMPARN</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f4f6f8;
            display: flex;
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
        }

        .main-content {
            flex: 1;
            padding: 30px;
            margin-left: 250px; /* largura padrão do menu lateral */
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 20px;
            }
        }

        .card-img-top {
            height: 200px;
            object-fit: cover;
        }

        .noticia-link {
            text-decoration: none;
            color: inherit;
        }
    </style>
</head>
<body>
    <!-- Menu reutilizável -->
    <?php include '../templates/gen_menu.php'; ?>

    <!-- Conteúdo principal -->
    <main class="main-content">
        <div class="container">
            <!-- Barra de busca -->
            <form method="GET" class="d-flex justify-content-center mb-4">
                <input type="text" name="q" 
                       class="form-control w-50 me-2 shadow-sm" 
                       placeholder="Pesquisar notícias..." 
                       value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>">
                <button type="submit" class="btn btn-primary shadow-sm">Buscar</button>
            </form>

            <!-- Título -->
            <h2 class="text-center mb-4 text-primary">📰 Notícias Existentes no Sistema</h2>

            <!-- Grid de notícias -->
            <div class="row row-cols-1 row-cols-md-3 g-4">
                <?php if (count($result_todas) > 0): ?>
                    <?php foreach ($result_todas as $row): ?>
                    <div class="col">
                        <div class="card h-100 shadow-sm">
                            <a href="<?= htmlspecialchars($row['link']) ?>" target="_blank" class="noticia-link">
                                <img src="/tasks_projects/<?= htmlspecialchars($row['imagem']) ?>" 
                                     class="card-img-top" 
                                     alt="<?= htmlspecialchars($row['titulo']) ?>">
                            </a>
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($row['titulo']) ?></h5>
                                <p class="card-text"><?= htmlspecialchars($row['subtitulo']) ?></p>
                                <small class="text-muted">
                                    Publicado em <?= date('d/m/Y', strtotime($row['data_publicacao'])) ?>
                                </small>
                            </div>
                            <div class="card-footer d-flex justify-content-between">
                                <a href="editar_noticia.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">
                                    ✏️ Editar
                                </a>
                                <a href="deletar_noticia.php?id=<?= $row['id'] ?>" 
                                   class="btn btn-danger btn-sm"
                                   onclick="return confirm('Tem certeza que deseja deletar esta notícia?');">
                                    🗑️ Deletar
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="alert alert-info text-center">
                            Nenhuma notícia encontrada no sistema.
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <footer class="mt-4 text-center">
        <small>© <?= date('Y') ?> EMPARN - Painel de Controle</small>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
