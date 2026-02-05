<?php
require_once __DIR__ . '/../config/connection.php';

$stmt = $pdo->query("
    SELECT 
        id,
        titulo,
        data_inicio,
        data_fim,
        status,
        TO_CHAR(data_criacao, 'DD/MM/YYYY') AS data_criacao,
        TO_CHAR(data_modificacao, 'DD/MM/YYYY') AS data_modificacao
    FROM projeto
    ORDER BY id DESC
");

$projetos = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        width: 80%;
    }

    #form-box {
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

    .form-box {
        max-width: 520px;
        margin: 60px auto;
        background: #ffffff;
        border-radius: 14px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.08);
        padding: 28px;
        position: relative;
    }

    .form-close {
        position: absolute;
        top: 12px;
        right: 12px;
        border: none;
        background: transparent;
        font-size: 20px;
        color: #6c757d;
        cursor: pointer;
        transition: 0.2s;
    }

    .form-close:hover {
        color: #dc3545;
        transform: scale(1.1);
    }

    .form-title {
        font-weight: 600;
        font-size: 18px;
        margin-bottom: 20px;
        color: #343a40;
    }

    .form-label {
        font-size: 14px;
        font-weight: 500;
        margin-bottom: 6px;
        color: #495057;
    }

    .form-control {
        border-radius: 8px;
        padding: 10px 12px;
        font-size: 14px;
        border: 1px solid #dee2e6;
        transition: 0.2s;
    }

    .form-control:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.15rem rgba(13,110,253,0.25);
    }

    .datas {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }

    .form-group {
        margin-bottom: 16px;
    }

    .btn-projeto {
        width: 100%;
        margin-top: 10px;
        padding: 12px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 15px;
        background: #0d6efd;
        border: none;
        color: #fff;
        transition: 0.2s;
    }

    .btn-projeto:hover {
        background: #0b5ed7;
        transform: translateY(-1px);
        box-shadow: 0 6px 12px rgba(13,110,253,0.25);
    }
    .menu-acoes{
        position:absolute;
        background:white;
        border:1px solid #ddd;
        border-radius:6px;
        box-shadow:0 6px 12px rgba(0,0,0,.15);
        padding:6px 0;
        z-index:9999;
    }

    .menu-acoes button{
        display:block;
        width:100%;
        padding:8px 14px;
        border:none;
        background:none;
        text-align:left;
    }

    .menu-acoes button:hover{
        background:#f1f1f1;
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
                        <th>Nome Projeto</th>

                        <th>Data Criação</th>

                        <th>Data Modificação</th>

                        <th>Status</th>

                        <th>Início</th>

                        <th>Fim</th>

                        <th style="width:160px;">Ações</th>
                    </tr>
                </thead>

                <tbody id="tbodyProjeto">

                <?php foreach ($projetos as $p): ?>
                    <tr>

                        <td>
                            <span class="valor" data-id="<?= $p['id'] ?>" data-campo="titulo">
                                <?= htmlspecialchars($p['titulo']) ?>
                            </span>
                            <button
                                class="btn btn-sm btn-outline-primary ms-1"
                                onclick="editarCampo(<?= $p['id'] ?>, 'titulo')"
                                title="Editar título">
                                <i class="bi bi-pencil"></i>
                            </button>

                        </td>

                        <td>
                            <?= htmlspecialchars($p['data_criacao']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($p['data_modificacao']) ?>

                        </td>

                        <td>
                            <?php if ($p['status'] == 'Em andamento'):?>
                                <span class="valor"
                                    data-id="<?= $p['id'] ?>"
                                    data-campo="status"
                                    style="color: <?= $p['status']=='Concluído'?'green':'red' ?>">
                                    <?= htmlspecialchars($p['status']) ?>
                                </span>
                                <button
                                    class="btn btn-sm btn-outline-primary ms-1"
                                    onclick="editarCampo(<?= $p['id'] ?>, 'status')"
                                    title="Editar status">
                                    <i class="bi bi-pencil"></i>
                                </button>
                            <?php endif?>
                        </td>

                        <td>
                            <span class="valor" data-id="<?= $p['id'] ?>" data-campo="data_inicio">
                                <?= date('d/m/Y', strtotime($p['data_inicio'])) ?>
                            </span>
                            <button
                                class="btn btn-sm btn-outline-primary ms-1"
                                onclick="editarCampo(<?= $p['id'] ?>, 'data_inicio')"
                                title="Editar data início">
                                <i class="bi bi-pencil"></i>
                            </button>

                        </td>

                        <td>
                            <span class="valor" data-id="<?= $p['id'] ?>" data-campo="data_fim">
                                <?= date('d/m/Y', strtotime($p['data_fim'])) ?>
                            </span>
                            <button
                                class="btn btn-sm btn-outline-primary ms-1"
                                onclick="editarCampo(<?= $p['id'] ?>, 'data_fim')"
                                title="Editar data fim">
                                <i class="bi bi-pencil"></i>
                            </button>
                        </td>

                        <td>
                            <button
                                class="btn btn-sm btn-secondary"
                                onclick="abrirMenu(event, <?= $p['id'] ?>)"
                                title="Opções">
                                <i class="bi bi-gear"></i>
                            </button>
                        </td>

                    </tr>
                <?php endforeach; ?>

            </tbody>
            

            </table>

        </div>

    </div>

</div>

</div>

<script>

    function AddProjeto(){

        if (document.getElementById("form-box")) {
            console.log("Form já está aberto");
            return;
        }

        const formBox = document.createElement("div");
        formBox.className = "form-box";
        formBox.id = "form-box";

        // botão fechar
        const btnClose = document.createElement("button");
        btnClose.className = "form-close";

        const iconClose = document.createElement("i");
        iconClose.className = "bi bi-x-lg";

        btnClose.appendChild(iconClose);
        formBox.appendChild(btnClose);

        btnClose.addEventListener("click", () => {
            formBox.remove();
        });

        // form
        const form = document.createElement("form");

        form.addEventListener("submit", async function(e){
            e.preventDefault();

            const dados = {
                titulo: document.getElementById("titulo").value,
                descricao: document.getElementById("descricao").value,
                data_inicio: document.getElementById("data_inicio").value,
                data_fim: document.getElementById("data_fim").value
            };

            try {
                const resposta = await fetch("criar-projeto", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify(dados)
                });

                const resultado = await resposta.json();

                if (resultado.status === "success") {

                    alert(resultado.message);
                    formBox.remove();location.reload();


                } else {

                    alert("Erro: " + resultado.message);

                }

            } catch (erro) {
                console.error("Falha:", erro);
                alert("Erro de conexão com o servidor.");
            }
        });


        // título
        const title = document.createElement("div");
        title.className = "form-title";
        title.textContent = "Cadastro de Projeto";
        form.appendChild(title);

        function criarCampo(labelText, id, type = "text") {
            const group = document.createElement("div");
            group.className = "form-group";

            const label = document.createElement("label");
            label.className = "form-label";
            label.setAttribute("for", id);
            label.textContent = labelText;

            const input = document.createElement("input");
            input.type = type;
            input.id = id;
            input.className = "form-control";

            group.appendChild(label);
            group.appendChild(input);

            return group;
        }

        form.appendChild(criarCampo("Título do Projeto", "titulo"));
        form.appendChild(criarCampo("Descrição do Projeto", "descricao"));

        const datas = document.createElement("div");
        datas.className = "datas";

        datas.appendChild(criarCampo("Data de Início", "data_inicio", "date"));
        datas.appendChild(criarCampo("Data Final", "data_fim", "date"));

        form.appendChild(datas);

        const btnAdd = document.createElement("button");
        btnAdd.type = "submit";
        btnAdd.className = "btn btn-projeto";

        const iconAdd = document.createElement("i");
        iconAdd.className = "bi bi-plus-circle me-2";

        btnAdd.appendChild(iconAdd);
        btnAdd.append("Adicionar Projeto");

        form.appendChild(btnAdd);

        formBox.appendChild(form);

        document.body.appendChild(formBox);

    }

    function abrirMenu(event, id){

        document.querySelectorAll(".menu-acoes").forEach(m => m.remove());

        const menu = document.createElement("div");
        menu.className = "menu-acoes";

        menu.innerHTML = `
            <button onclick="excluirProjeto(${id})">
                <span style="color: red;"><i class="bi bi-trash"></i> Excluir Projeto</span>
            </button>
            <button onclick="viewPanTarefa(${id})">
                <i class="bi bi-list-check"></i> Gerenciar Tarefas
            </button>
            <button onclick="viewPanUs(${id})">
                <i class="bi bi-people"></i> Administrar Usuários
            </button>
        `;

        document.body.appendChild(menu);

        const rect = event.target.closest("button").getBoundingClientRect();
        menu.style.top = rect.bottom + "px";
        menu.style.left = rect.left + "px";

        document.addEventListener("click", () => menu.remove(), { once: true });

        event.stopPropagation();
    }

    function excluirProjeto(id){
        if(confirm("Deseja excluir este projeto?")){
            alert("Excluir projeto " + id);
        }
    }

    function editarCampo(id, campo){

        const span = document.querySelector(
            `.valor[data-id="${id}"][data-campo="${campo}"]`
        );

        if (!span) return;

        const valorAtual = span.innerText.trim();

        let input;

        // STATUS = SELECT
        if (campo === "status") {

            input = document.createElement("select");
            input.className = "form-control form-control-sm";

            ["Em andamento", "Concluído"].forEach(op => {
                const option = document.createElement("option");
                option.value = op;
                option.textContent = op;
                if (op === valorAtual) option.selected = true;
                input.appendChild(option);
            });

            setTimeout(() => {
                input.focus();
                input.click();  
            }, 50);
        }

        // DATAS = DATE
        else if (campo === "data_inicio" || campo === "data_fim") {

            input = document.createElement("input");
            input.type = "date";
            input.className = "form-control form-control-sm";

            // converte dd/mm/yyyy -> yyyy-mm-dd
            const partes = valorAtual.split("/");
            if (partes.length === 3) {
                input.value = `${partes[2]}-${partes[1]}-${partes[0]}`;
            }

        }

        // TEXTO
        else {
            input = document.createElement("input");
            input.type = "text";
            input.value = valorAtual;
            input.className = "form-control form-control-sm";
        }

        span.replaceWith(input);
        input.focus();

        function salvar(){

            let novoValor = input.value;

            fetch("editar-projeto", {
                method: "POST",
                headers: {"Content-Type":"application/json"},
                body: JSON.stringify({
                    id: id,
                    campo: campo,
                    valor: novoValor
                })
            })
            .then(r => r.json())
            .then(resp => {

                if (resp.status !== "success") {
                    alert(resp.message);
                    return;
                }

                const novoSpan = document.createElement("span");
                novoSpan.className = "valor";
                novoSpan.dataset.id = id;
                novoSpan.dataset.campo = campo;

                if (campo === "status") {
                    novoSpan.style.color =
                        novoValor === "Concluído" ? "green" : "red";
                }

                // converte data de volta
                if (campo.includes("data")) {
                    const d = new Date(novoValor);
                    novoValor =
                        d.toLocaleDateString("pt-BR");
                }

                novoSpan.textContent = novoValor;
                input.replaceWith(novoSpan);
            });
        }

        input.addEventListener("blur", salvar);
        input.addEventListener("keydown", e => {
            if (e.key === "Enter") {
                e.preventDefault();
                salvar();
            }
        });
    }

</script>

</body>
</html>
