<?php
#echo 'Esta é a página de gerenciamento de Projetos e Tarefas';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<!-- Bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

<title>Gerenciamento de Projetos e Tarefas</title>

<style>
body, html {
    margin: 0;
    height: 100%;
}

.pai{
    display: flex;
}

/* HEADER / FERRAMENTAS */
.ferramentas{
    display: flex;
    align-items: center;
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    width: 100%;
    min-height: 80px;
    flex-shrink: 0;
}

.opcoes{
    display: flex;
    gap: 15px;
    margin: 20px;
}

/* SIDEBAR */
.sidebar {
    width: 250px;
    height: 100%;
    background: #1e1e2f;
    color: #fff;
    padding: 20px;
    box-shadow: 2px 0 8px rgba(0,0,0,0.2);
    font-family: 'Segoe UI', Arial, sans-serif;
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
    color: #20c997;
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

/* CONTEÚDO */
.ferramentas-conteudos{
    display: flex;
    flex-direction: column;
    flex: 1;
    height: 100%;
}

.conteudo{
    display: flex;
    width: 100%;
    background-color: #eef2f7;
    padding: 20px;
    gap: 20px;
}

.projetos,
.tarefas{
    width: 50%;
}

.caixa-flutuante {
    position: fixed;
    z-index: 9999;

    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);

    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.3);
}

</style>
</head>

<body>

<div class="pai">

<aside class="sidebar">
    <h2>Controle</h2>
    <nav>
        <ul class="menu">
            <li class="menu-section">Principal</li>
            <li><a href="control" class="<?= $pagina === 'control' ? 'active' : '' ?>"><i class="bi bi-house"></i> Home Controle</a></li>
            
            <li class="menu-section">Usuário</li>
            <li><a href="cadastrar-usuario" ><i class="bi bi-plus-circle"></i> Novo Usuário</a></li>
            <li><a href="listar-usuarios"><i class="bi bi-eye"></i> Usuários Existentes</a></li>
            
            <li class="menu-section">Classes de Usuário</li>
            <li><a href="criar-classe"><i class="bi bi-plus-circle"></i> Nova Classe</a></li>
            <li><a href="listar-classes"><i class="bi bi-eye"></i> Classes Existentes</a></li>

            <li class="menu-section">Áreas de Atuação</li>
            <li><a href="criar-nova-area"><i class="bi bi-plus-circle"></i> Nova Área</a></li>
            <li><a href="listar-areas-existentes"><i class="bi bi-eye"></i> Áreas Existentes</a></li>

            <li class="menu-section">Notícias</li>
            <li><a href="cadastrar-noticias"><i class="bi bi-plus-circle"></i> Nova Notícia</a></li>
            <li><a href="view-noticias-existentes"><i class="bi bi-eye"></i> Ver Notícias</a></li>

            <li class="menu-section">Dashboard</li>
            <li><a href="gerenciador-de-dashboards"><i class="bi bi-clipboard-data"></i> Gerenciar Dashboard</a></li>

            <li class="menu-section">Documentos</li>
            <li><a href="gerenciar-documentos-institucionais"><i class="bi bi-file-text"></i> Gerenciar Documentos</a></li>

            <li class="menu-section">Projetos & Tarefas</li>
            <li><a href="ger-projetos-tarefas"><i class="bi bi-kanban-fill service-icon"></i>
            Gerenciar Projetos & Tarefas</a></li>
            
            <li class="menu-section">Site Público</li>
            <li><a href="home"><i class="bi bi-arrow-left-circle"></i> Voltar ao Site</a></li>
            
        </ul>
        <br>
        <br>
    </nav>
</aside>

<div class="ferramentas-conteudos">

    <!-- BARRA DE FERRAMENTAS -->
    <div class="ferramentas shadow-sm">

        <div class="opcoes">

            <button class="btn btn-primary" onclick="AddProjeto()">
                <i class="bi bi-folder-plus"></i> Criar Projeto
            </button>

            <button class="btn btn-success" onclick="AddTarefa()">
                <i class="bi bi-plus-circle"></i> Adicionar Tarefa
            </button>

        </div>

    </div>

    <!-- CONTEÚDO -->
    <div class="conteudo">

        <!-- PROJETOS -->
        <div class="projetos">

            <h5 class="mb-3">
                <i class="bi bi-kanban"></i> Projetos
            </h5>

            <table class="table table-hover table-striped shadow-sm bg-white rounded">

                <thead class="table-dark">
                    <tr>
                        <th>Nome do Projeto</th>
                    </tr>
                </thead>

                <tbody id="tbodyProjeto">
                </tbody>

            </table>

        </div>

        <!-- TAREFAS -->
        <div class="tarefas">

            <h5 class="mb-3">
                <i class="bi bi-list-check"></i> Tarefas
            </h5>

            <table class="table table-hover table-striped shadow-sm bg-white rounded">

                <thead class="table-dark">
                    <tr>
                        <th>Nome da Tarefa</th>
                    </tr>
                </thead>

                <tbody id="tbodyTarefa">
                </tbody>

            </table>

        </div>

    </div>

</div>

</div>

<script>

    function AddProjeto(){

        const caixaInterface = document.createElement("div");
        caixaInterface.classList.add("caixa-flutuante");

        caixaInterface.textContent = "Esta é a caixa para criação de Projetos";
        document.body.appendChild(caixaInterface);

    }

    function AddTarefa(){

        const caixaInterface = document.createElement("div");
        caixaInterface.classList.add("caixa-flutuante");

        caixaInterface.textContent = "Esta é a caixa para criação de Tarefas";
        document.body.appendChild(caixaInterface);
    }
    
</script>

</body>
</html>
