<?php $pagina = basename($_SERVER['PHP_SELF']); ?>

<style>
/* Escopo apenas para a sidebar */
/* Sidebar container */
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
    font-family: Arial, sans-serif;
    z-index: 1000;
}

.sidebar h2 {
    text-align: center;
    margin-bottom: 20px;
    font-size: 22px;
    color: #f2f2f2;
}

.sidebar .menu {
    list-style: none;
    padding: 0;
    margin: 0;
}

.sidebar .menu-section {
    margin: 15px 0 5px;
    font-size: 14px;
    text-transform: uppercase;
    color: #aaa;
    font-weight: bold;
}

.sidebar .menu li a {
    display: block;
    padding: 10px 15px;
    margin: 5px 0;
    color: #ddd;
    text-decoration: none;
    border-radius: 4px;
    transition: all 0.3s ease;
}

.sidebar .menu li a:hover {
    background: #2e2e4d;
    color: #fff;
}

.sidebar .menu li a.active {
    background: #4a90e2;
    color: #fff;
    font-weight: bold;
}

</style>

<aside class="sidebar">
    <h2>Controle</h2>
    <nav>
        <ul class="menu">
            <li><a href="index_admin.php" class="<?= $pagina === 'index_admin.php' ? 'active' : '' ?>">Início</a></li>

            <li class="menu-section">Usuário</li>
            <li><a href="../controle/cadastrar_us.php" class="<?= $pagina === '../controle/cadastrar_us.php' ? 'active' : '' ?>">Criar Usuário</a></li>
            <li><a href="../controle/get_us.php" class="<?= $pagina === '../controle/get_us.php' ? 'active' : '' ?>">Ver Usuários <br> Existentes</a></li>
            <li><a href="../controle/deletar_us.php" class="<?= $pagina === '../controle/deletar_us.php' ? 'active' : '' ?>">Deletar Usuário</a></li>
            <li><a href="../controle/update_us.php" class="<?= $pagina === '../controle/update_us.php' ? 'active' : '' ?>">Mudar Dados de Usuário</a></li>

            <li class="menu-section">Classes de Usuário</li>
            <li><a href="criar_classe.php" class="<?= $pagina === 'criar_classe.php' ? 'active' : '' ?>">Criar Classe</a></li>
            <li><a href="listar_classes.php" class="<?= $pagina === 'listar_classes.php' ? 'active' : '' ?>">Ver Classes <br> Existentes</a></li>
            <li><a href="deletar_classe.php" class="<?= $pagina === 'deletar_classe.php' ? 'active' : '' ?>">Deletar Classe</a></li>

            <li class="menu-section">Áreas de Atuação</li>
            <li><a href="criar_area.php" class="<?= $pagina === 'criar_area.php' ? 'active' : '' ?>">Criar Área de Atuação</a></li>
            <li><a href="listar_area.php" class="<?= $pagina === 'listar_area.php' ? 'active' : '' ?>">Ver Áreas de Atuação Existentes</a></li>
            <li><a href="deletar_area.php" class="<?= $pagina === 'deletar_area.php' ? 'active' : '' ?>">Deletar Área de Atuação</a></li>
        </ul>
    </nav>
</aside>
