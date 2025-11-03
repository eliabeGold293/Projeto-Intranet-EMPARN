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
        $resposta = $admin->addClassUs($nome);
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Criar Classe de Usuário</title>
    <link rel="stylesheet" href="../static/criar-classe.css">
</head>
<body>
    <div class="container">
        <aside class="sidebar">
            <h2>Admin</h2>
            <nav>
                <ul>
                    
                </ul>
            </nav>
        </aside>

        <main class="content">
            <h1>Criar Nova Classe de Usuário</h1>
            <form method="POST">
                <label for="nome">Nome da Classe:</label>
                <input type="text" name="nome" id="nome" required>
                <button type="submit">Criar Classe</button>
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
