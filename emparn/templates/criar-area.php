<?php
require_once '../config/connection.php';
require_once '../controllers/admin.php';

$admin = new Admin($pdo);
$resposta = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome = trim($_POST["nome"]);
    if ($nome === "") {
        $resposta = "Por favor, insira um nome válido.";
    } else {
        $resposta = $admin->addAreaAtuacaoUs($nome);
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Criar Área de Atuação</title>
    <link rel="stylesheet" href="../static/criar-area.css">
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
            <h1>Criar Nova Área de Atuação</h1>
            <form method="POST">
                <label for="nome">Nome da Área:</label>
                <input type="text" name="nome" id="nome" required>
                <button type="submit">Criar Área</button>
            </form>

            <?php if ($resposta): ?>
                <div class="resposta">
                    <p><?php echo htmlspecialchars($resposta); ?></p>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
