<?php

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel Admin</title>
    <link rel="stylesheet" href="../static/index-admin.css">
</head>
<body>
    <div class="container">
        <aside class="sidebar">
            <h2>Admin</h2>
                <nav>
                    <ul class="menu">
                        <li class="menu-section">Usuário</li>
                        <li><a href="#criar-usuario">Criar Usuários</a></li>
                        <li><a href="#ver-usuarios">Ver Usuários</a></li>

                        <li class="menu-section">Classes de Usuário</li>
                        <li><a href="criar-classe.php">Criar Classe</a></li>
                        <li><a href="listar-classes.php">Ver Classes</a></li>

                        <li class="menu-section">Áreas de Atuação</li>
                        <li><a href="criar-area.php">Criar Área de Atuação</a></li>
                        <li><a href="listar-area.php">Ver Áreas de Atuação</a></li>
                    </ul>
                </nav>
        </aside>

        <main class="content">
            <h1>Painel de Controle</h1>
        </main>
    </div>
</body>
</html>
