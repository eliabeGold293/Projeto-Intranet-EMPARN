<?php
require_once '../config/connection.php';
require_once '../controllers/admin.php';

$admin = new Admin($pdo);
$areas = $admin->viewAreaAtuacaoUs();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Áreas de Atuação</title>
    <link rel="stylesheet" href="../static/listar-area.css">
</head>
<body>
    <div class="container">
        <aside class="sidebar">
            <h2>Admin</h2>
            <nav>
                <ul class="menu">
        
                </ul>
            </nav>
        </aside>

        <main class="content">
            <h1>Áreas de Atuação Cadastradas</h1>

            <?php if (count($areas) > 0): ?>
                <ul class="lista">
                    <?php foreach ($areas as $area): ?>
                        <li><?php echo htmlspecialchars($area['nome']); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>Nenhuma área de atuação cadastrada.</p>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
