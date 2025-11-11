<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Index</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            display: flex;
            min-height: 100vh;
        }

        /* Estilo do menu lateral */
        .sidebar {
            width: 220px;
            background-color: #2c3e50;
            color: #fff;
            display: flex;
            flex-direction: column;
            padding-top: 20px;
            position: fixed;
            top: 0;
            left: 0;
            height: 100%;
            transition: transform 0.3s ease;
        }

        .sidebar h2 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 20px;
            border-bottom: 1px solid #444;
            padding-bottom: 10px;
        }

        .sidebar a {
            padding: 15px 20px;
            text-decoration: none;
            color: #fff;
            display: block;
            transition: background 0.3s;
        }

        .sidebar a:hover {
            background-color: #34495e;
        }

        /* Conteúdo principal */
        .content {
            margin-left: 220px;
            padding: 20px;
            flex: 1;
        }

        /* Responsividade */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.active {
                transform: translateX(0);
            }
            .content {
                margin-left: 0;
            }
            .menu-toggle {
                position: fixed;
                top: 15px;
                left: 15px;
                background: #2c3e50;
                color: #fff;
                border: none;
                padding: 10px 15px;
                cursor: pointer;
                z-index: 1000;
            }
        }
    </style>
</head>
<body>

    <!-- Botão para abrir/fechar menu em telas pequenas -->
    <button class="menu-toggle" onclick="toggleMenu()">☰ Menu</button>

    <!-- Menu lateral -->
    <div class="sidebar" id="sidebar">
        <h2>Serviços</h2>
        <a href="tasks_projects/public/login.php">📂 Gerenciamento de Tarefas e Projetos</a>
        <a href="#link2">⚙️ $$$$$ </a>
    </div>

    <!-- Conteúdo principal -->
    <div class="content">
        <h1>EMPARN</h1>
        <p>Painel do Colaborador</p>
    </div>

    <script>
        function toggleMenu() {
            document.getElementById("sidebar").classList.toggle("active");
        }
    </script>
</body>
</html>
