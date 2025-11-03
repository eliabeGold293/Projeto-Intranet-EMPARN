<?php
require_once '../config/connection.php';
require_once '../controllers/admin.php';

$admin = new Admin($pdo);
$classes = $admin->viewClassUs();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Ver Classes</title>
    <link rel="stylesheet" href="../static/listar-classes.css">
</head>
<body>
    <div class="container">
        <aside class="sidebar">
            <h2>Admin</h2>
            <nav>
                <ul>
                    <li><a href="index-admin.php">Voltar ao Início</a></li>
                </ul>
            </nav>
        </aside>

        <main class="content">
            <h1>Lista de Classes de Usuário Existentes no Sistema</h1>

            <?php if (count($classes) > 0): ?>
                <ul class="lista">
                    <?php foreach ($classes as $classe): ?>
                        <li><?php echo htmlspecialchars($classe['nome']); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>Nenhuma classe cadastrada.</p>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
