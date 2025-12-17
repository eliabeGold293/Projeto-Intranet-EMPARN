<?php
require_once __DIR__ . '/../config/connection.php';

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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<style>
    .card-img-top {
        height: 200px;       /* altura fixa para todas as imagens */
        object-fit: cover;   /* corta proporcionalmente sem distorcer */
    }

    .noticia-link {
        text-decoration: none;
        color: inherit;
    }
</style>
<body>
    <?php include __DIR__ . '/../templates/header.php'; ?>
    <div class="container mt-5 mb-3">
        <form method="GET" class="d-flex justify-content-center">
            <input type="text" name="q" class="form-control w-50 me-2" placeholder="Pesquisar notícias..." value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>">
            <button type="submit" class="btn btn-primary shadow-sm">Buscar</button>
        </form>
    </div>
    <div class="container mt-5 mb-5">
        <h2 class="text-center mb-4">Confira Todas as Nossas Notícias</h2>
        <div class="row row-cols-1 row-cols-md-3 g-4">
            <?php foreach ($result_todas as $row): ?>
            <div class="col">
                <a href="noticia-gen?id=<?= $row['id'] ?>" style="text-decoration:none; color:inherit;">
                    <div class="card h-100 shadow-sm">
                        <img src="/tasks_projects/uploads/<?= htmlspecialchars($row['imagem']) ?>" class="card-img-top" alt="<?= htmlspecialchars($row['titulo']) ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($row['titulo']) ?></h5>
                            <p class="card-text"><?= htmlspecialchars($row['subtitulo']) ?></p>
                            <small class="text-muted">Publicado em <?= date('d/m/Y', strtotime($row['data_publicacao'])) ?></small>
                        </div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <footer class="text-center bg-dark text-white py-3">
        <p>© <?= date('Y') ?> EMPARN - Todos os direitos reservados.</p>
    </footer>
</body>
</html>
