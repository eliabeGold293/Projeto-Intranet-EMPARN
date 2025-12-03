<?php 
$pagina = basename($_SERVER['PHP_SELF']); 
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">

<style>
    .sidebar {
        position: fixed;
        top: 0;
        left: 0;
        width: 250px;
        height: 100%;
        background: #1e1e2f;
        color: #fff;
        padding: 20px;
        box-shadow: 2px 0 8px rgba(0,0,0,0.2);
        font-family: 'Segoe UI', Arial, sans-serif;
        z-index: 1000;
        overflow-y: auto;
    }

    .sidebar h2 {
        text-align: center;
        margin-bottom: 20px;
        font-size: 1.4rem;
        color: #f8f9fa;
    }

    .sidebar .menu {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .sidebar .menu-section {
        margin: 15px 0 5px;
        font-size: 0.85rem;
        text-transform: uppercase;
        color: #20c997; /* verde bootstrap */
        font-weight: bold;
    }

    .sidebar .menu li a {
        display: block;
        padding: 10px 15px;
        margin: 5px 0;
        color: #adb5bd;
        text-decoration: none;
        border-radius: 4px;
        transition: all 0.3s ease;
    }

    .sidebar .menu li a:hover,
    .sidebar .menu li a.active {
        background: #2e2e4d;
        color: #fff;
        font-weight: 500;
    }

    .bi {
        font-size: 1.2rem;
        margin-right: 8px;
        color: #0d6efd; /* azul bootstrap */
    }
</style>

<aside class="sidebar">
    <h2>Controle</h2>
    <nav>
        <ul class="menu">
            <li class="menu-section">Principal</li>
            <li><a href="index_controle.php" class="<?= $pagina === 'index_controle.php' ? 'active' : '' ?>"><i class="bi bi-house"></i> Home Controle</a></li>
            
            <li class="menu-section">Usuário</li>
            <li><a href="cadastrar_us.php" class="<?= $pagina === 'cadastrar_us.php' ? 'active' : '' ?>"><i class="bi bi-plus-circle"></i> Novo Usuário</a></li>
            <li><a href="get_us.php" class="<?= $pagina === 'get_us.php' ? 'active' : '' ?>"><i class="bi bi-eye"></i> Usuários Existentes</a></li>
            
            <li class="menu-section">Classes de Usuário</li>
            <li><a href="criar_classe.php" class="<?= $pagina === 'criar_classe.php' ? 'active' : '' ?>"><i class="bi bi-plus-circle"></i> Nova Classe</a></li>
            <li><a href="listar_classes.php" class="<?= $pagina === 'listar_classes.php' ? 'active' : '' ?>"><i class="bi bi-eye"></i> Classes Existentes</a></li>

            <li class="menu-section">Áreas de Atuação</li>
            <li><a href="criar_area.php" class="<?= $pagina === 'criar_area.php' ? 'active' : '' ?>"><i class="bi bi-plus-circle"></i> Nova Área</a></li>
            <li><a href="listar_areas.php" class="<?= $pagina === 'listar_areas.php' ? 'active' : '' ?>"><i class="bi bi-eye"></i> Áreas Existentes</a></li>

            <li class="menu-section">Notícias</li>
            <li><a href="cadastro_noticias.php" class="<?= $pagina === 'cadastro_noticias.php' ? 'active' : '' ?>"><i class="bi bi-plus-circle"></i> Nova Notícia</a></li>
            <li><a href="get_noticias.php" class="<?= $pagina === 'get_noticias.php' ? 'active' : '' ?>"><i class="bi bi-eye"></i> Ver Notícias</a></li>

            <li class="menu-section">Dashboard</li>
            <li><a href="gerenciar_dashboard.php" class="<?= $pagina === 'gerenciar_dashboard.php' ? 'active' : '' ?>"><i class="bi bi-clipboard-data"></i> Gerenciar Dashboard</a></li>

            <li class="menu-section">Documentos</li>
            <li><a href="../controle/documentos.php" class="<?= $pagina === '../controle/documentos.php' ? 'active' : '' ?>"><i class="bi bi-file-text"></i> Gerenciar Documentos</a></li>
            
            <li class="menu-section">Site Público</li>
            <li><a href="../public/index.php" class="<?= $pagina === 'index.php' ? 'active' : '' ?>"><i class="bi bi-arrow-left-circle"></i> Voltar ao Site</a></li>
        </ul>
        <br>
        <br>
    </nav>
</aside>
