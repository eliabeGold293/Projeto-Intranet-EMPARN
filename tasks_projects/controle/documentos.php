<?php
session_start();

// Impedir cache da página protegida
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Impedir navegação "voltar" após logout
header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");

// Se não estiver logado → volta para login
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['grau_acesso'])) {
    header("Location: login");
    # echo 'Não há usuário logado';
    exit;
}

require_once __DIR__ . '/../config/connection.php';

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Administração de Documentos</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">

    <style>
        body {
            background-color: #eef1f4;
            margin: 0;
            display: flex;
        }

        .main-content {
            flex: 1;
            padding: 35px;
            margin-left: 250px;
        }

        @media(max-width: 768px) {
            .main-content {
                margin-left: 0;
            }
        }

        .page-title {
            font-size: 2rem;
            font-weight: 700;
            color: #0046a0;
            margin-bottom: 25px;
        }

        .card-modern {
            background: #fff;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
            border: none;
        }

        .topic-box {
            border: 2px dashed #0d6efd;
            border-radius: 10px;
            padding: 30px;
            text-align: center;
            cursor: pointer;
            transition: 0.3s;
        }

        .topic-box:hover {
            background: #e8f1ff;
        }

        .topic-form {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            border: 1px solid #d8d8d8;
            margin-bottom: 20px;
        }

        .remove-topic {
            float: right;
            cursor: pointer;
        }

        .file-row {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
        }

        /* Caixa da tabela com barra de rolagem */
        .table-scroll {
            max-height: 400px;
            overflow-y: auto;
            border: 1px solid #d8d8d8;
            border-radius: 8px;
        }

        /* Cabeçalho fixo */
        .table-scroll thead th {
            position: sticky;
            top: 0;
            background: #cfe2ff !important;
            z-index: 5;
        }

        /* Espaçamento interno do modal */
        #addFileModal .modal-body {
            padding: 20px 25px; /* aumenta o padding lateral e vertical */
        }

        /* Espaçamento entre elementos do modal */
        #addFileModal .mb-3 {
            margin-bottom: 1rem; /* garante distância entre label e input/select */
        }

        /* Botões do modal */
        #addFileModal .modal-footer {
            justify-content: flex-end; /* alinhamento padrão à direita */
            gap: 10px; /* espaço entre os botões */
        }

        /* Alinhamento do select e input para ocupar toda a largura */
        #addFileModal .form-control,
        #addFileModal .form-select {
            width: 100%;
        }
    </style>
</head>
<!-- MODAL ADICIONAR ARQUIVO -->
<div class="modal fade" id="addFileModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Adicionar Arquivo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="mb-3">
                    <label for="addFileTopicSelect" class="form-label">Selecione o Tópico</label>
                    <select id="addFileTopicSelect" class="form-select"></select>
                </div>

                <div class="mb-3">
                    <label for="addFileInput" class="form-label">Escolha o Arquivo</label>
                    <input type="file" id="addFileInput" class="form-control">
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-primary" onclick="enviarNovoArquivoParaTopico()">Salvar</button>
            </div>

        </div>
    </div>
</div>
<!-- MODAL EDITAR ARQUIVO -->
<div class="modal fade" id="editFileModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Substituir Arquivo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <input type="file" id="newFileInput" class="form-control">
                <input type="hidden" id="editFileId">
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-primary" onclick="enviarNovoArquivo()">Salvar</button>
            </div>

        </div>
    </div>
</div>

<body>
    <?php include __DIR__ . '/../templates/gen_menu.php'; ?>

    <main class="main-content">

        <h2 class="page-title">
            <i class="bi bi-folder-plus"></i> Administração de Documentos Institucionais
        </h2>

        <div class="card-modern">

            <div id="topicsContainer"></div>

            <!-- Botão criar tópico -->
            <div class="topic-box mt-4" id="addTopicBtn">
                <i class="bi bi-plus-circle" style="font-size: 2.5rem; color: #0d6efd;"></i>
                <p class="mt-2 mb-0">Adicionar Novo Tópico de Documentos</p>
            </div>

            <!-- Botão final -->
            <button id="saveAllBtn" class="btn btn-success mt-4">
                <i class="bi bi-check2-circle"></i> Salvar Tudo
            </button>

            <div id="message" class="mt-3"></div>

        </div>

        <?php
        // Buscar tópicos com arquivos
        $query = "
        SELECT 
            t.id AS topico_id,
            t.nome AS topico_nome,
            t.data_criacao,
            t.data_modificacao,
            a.id AS arquivo_id,
            a.nome_original,
            a.caminho_armazenado,
            a.tipo,
            a.tamanho,
            a.data_upload
        FROM documento_topico t
        LEFT JOIN documento_arquivo a ON a.topico_id = t.id
        ORDER BY t.id DESC, a.id ASC
    ";

        $stmt = $pdo->query($query);
        $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Agrupar por tópico
        $topicos = [];
        foreach ($dados as $row) {
            $id = $row['topico_id'];

            if (!isset($topicos[$id])) {
                $topicos[$id] = [
                    'id' => $id,
                    'nome' => $row['topico_nome'],
                    'data_criacao' => $row['data_criacao'],
                    'data_modificacao' => $row['data_modificacao'],
                    'arquivos' => []
                ];
            }


            if ($row['arquivo_id']) {
                $topicos[$id]['arquivos'][] = [
                    'id' => $row['arquivo_id'],
                    'nome_original' => $row['nome_original'],
                    'caminho' => $row['caminho_armazenado'],
                    'tipo' => $row['tipo'],
                    'tamanho' => $row['tamanho'],
                    'data_upload' => $row['data_upload']
                ];
            }
        }
        ?>

        <hr class="my-4">

        <h4 class="mb-3"><i class="bi bi-table"></i> Tópicos Cadastrados</h4>

        <!-- CAMPO DE BUSCA -->
        <input
            type="text"
            id="searchInput"
            class="form-control mb-3"
            placeholder="Pesquisar por nome do tópico ou data (ex: 2024-11)...">

        <!-- TABELA COM ROLAGEM -->
        <div class="table-scroll">
            <table class="table table-striped table-hover" id="topicsTable">
                <thead class="table-primary">
                    <tr>
                        <th>Tópico</th>
                        <th>
                            Arquivos
                            <button class="btn btn-sm btn-outline-primary ms-2" 
                                    onclick="openAddFileModal(null)" 
                                    title="Adicionar arquivo">
                                <i class="bi bi-plus"></i>
                            </button>
                        </th>
                        <th>Criado / Modificado</th>
                        <th style="width: 120px;">Ações</th>
                    </tr>
                </thead>


                <tbody>
                    <?php if (count($topicos) === 0): ?>
                        <tr>
                            <td colspan="3" class="text-center text-muted">Nenhum tópico cadastrado.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($topicos as $topico): ?>
                            <tr>
                                <td>
                                    <span class="editable-topic" data-id="<?= $topico['id'] ?>">
                                        <?= htmlspecialchars($topico['nome']) ?>
                                    </span>
                                    <i class="bi bi-pencil-square text-primary ms-2 edit-topic" style="cursor:pointer;"></i>
                                </td>

                                <td>
                                    <?php if (count($topico['arquivos']) === 0): ?>
                                        <span class="text-muted">Nenhum arquivo</span>
                                    <?php else: ?>
                                        <ul class="mb-0">
                                            <?php foreach ($topico['arquivos'] as $arq): ?>
                                                <li>
                                                    <span class="editable-file" data-id="<?= $arq['id'] ?>">
                                                        <?= htmlspecialchars($arq['nome_original']) ?>
                                                    </span>

                                                    <i class="bi bi-pencil-square text-primary ms-2 edit-file" style="cursor:pointer;"></i>

                                                    <br>
                                                    <small class="text-muted">
                                                        (<?= $arq['tipo'] ?> • <?= round($arq['tamanho'] / 1024, 1) ?> KB)
                                                    </small>
                                                    <button class="btn btn-sm btn-danger ms-2" onclick="deleteFileFromDatabase(<?= $arq['id'] ?>)"><span style="color:white; font-weight:bold; font-size:0.9rem;">×</span></button>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <small>
                                        Criado: <strong><?= date("d/m/Y H:i", strtotime($topico['data_criacao'])) ?></strong><br>
                                        Modificado: <strong><?= date("d/m/Y H:i", strtotime($topico['data_modificacao'])) ?></strong>
                                    </small>
                                </td>

                                <td class="text-center">

                                    <button class="btn btn-sm btn-danger"
                                        onclick="deleteTopicFromDatabase(<?= $topico['id'] ?>)">
                                        <i class="bi bi-trash text-white"></i>
                                    </button>

                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        let topicCount = 0;

        document.getElementById("addTopicBtn").addEventListener("click", addTopic);

        // -----------------------------------------------------
        // ADICIONAR NOVO TÓPICO
        // -----------------------------------------------------
        function addTopic() {
            topicCount++;

            const container = document.getElementById("topicsContainer");

            const div = document.createElement("div");
            div.classList.add("topic-form");
            div.setAttribute("data-topic-id", topicCount);

            div.innerHTML = `
            <span class="remove-topic text-danger fw-bold" onclick="confirmRemoveTopic(${topicCount})" style="cursor:pointer">
                <i class="bi bi-x-lg"></i>
            </span>

            <h5 class="mb-3 topic-title">Tópico ${topicCount}</h5>

            <div class="mb-3">
                <label class="form-label">Nome do Tópico</label>
                <input type="text" class="form-control topic-name" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Descrição (opcional)</label>
                <textarea class="form-control topic-desc" rows="2"></textarea>
            </div>

            <div class="files-container mb-3"></div>

            <button type="button" class="btn btn-outline-primary btn-sm" onclick="addFile(${topicCount})">
                <i class="bi bi-paperclip"></i> Adicionar Documento
            </button>
        `;

            container.appendChild(div);
            reorderTopics();
        }

        // -----------------------------------------------------
        function confirmRemoveTopic(id) {
            if (confirm("Tem certeza que deseja remover este tópico?")) {
                removeTopic(id);
            }
        }

        function removeTopic(id) {
            const node = document.querySelector(`[data-topic-id="${id}"]`);
            if (node) node.remove();
            reorderTopics();
        }

        // -----------------------------------------------------
        function reorderTopics() {
            const topics = document.querySelectorAll(".topic-form");
            topicCount = topics.length;

            let newId = 1;
            topics.forEach(topic => {
                topic.setAttribute("data-topic-id", newId);

                const title = topic.querySelector(".topic-title");
                if (title) title.textContent = `Tópico ${newId}`;

                topic.querySelector(".remove-topic").setAttribute("onclick", `confirmRemoveTopic(${newId})`);

                topic.querySelector("button.btn-outline-primary")
                    .setAttribute("onclick", `addFile(${newId})`);

                newId++;
            });
        }

        // -----------------------------------------------------
        function addFile(topicId) {
            const fileContainer = document.querySelector(`[data-topic-id="${topicId}"] .files-container`);

            const row = document.createElement("div");
            row.classList.add("file-row", "d-flex", "gap-2", "mb-2", "align-items-center");

            row.innerHTML = `
            <input type="file" class="form-control file-input">
            <button type="button" class="btn btn-danger btn-sm" onclick="this.parentElement.remove()">
                <i class="bi bi-x-lg"></i>
            </button>
        `;

            fileContainer.appendChild(row);
        }

        // -----------------------------------------------------
        // ENVIO DOS DADOS – AGORA COM VALIDAÇÃO DE ARQUIVOS
        // -----------------------------------------------------
        document.getElementById("saveAllBtn").addEventListener("click", async () => {

            if (!confirm("Deseja realmente salvar todos os tópicos e arquivos?")) return;

            const topics = document.querySelectorAll(".topic-form");
            const messageDiv = document.getElementById("message");
            messageDiv.innerHTML = "";

            if (topics.length === 0) {
                messageDiv.innerHTML = `<div class="alert alert-warning">Adicione ao menos um tópico.</div>`;
                return;
            }

            const formData = new FormData();
            const topicsMeta = [];

            for (let index = 0; index < topics.length; index++) {
                const topic = topics[index];

                const nome = topic.querySelector(".topic-name").value.trim();
                const descricao = topic.querySelector(".topic-desc").value.trim();
                const fileInputs = topic.querySelectorAll(".file-input");

                // SE O TÓPICO NÃO TIVER NOME
                if (!nome) {
                    messageDiv.innerHTML = `<div class="alert alert-warning">O tópico ${index + 1} precisa de um nome.</div>`;
                    return;
                }

                // OBRIGATORIEDADE: PELO MENOS 1 ARQUIVO
                let hasFile = false;
                fileInputs.forEach(fi => {
                    if (fi.files.length > 0) hasFile = true;
                });

                if (!hasFile) {
                    messageDiv.innerHTML = `<div class="alert alert-danger">O tópico ${index + 1} precisa ter ao menos um arquivo anexado.</div>`;
                    return;
                }

                // Monta metadados
                topicsMeta.push({
                    nome,
                    descricao
                });

                // arquivos files_topic_0, files_topic_1...
                fileInputs.forEach(fi => {
                    for (let k = 0; k < fi.files.length; k++) {
                        formData.append(`files_topic_${index}[]`, fi.files[k]);
                    }
                });
            }

            formData.append("topics_meta", JSON.stringify(topicsMeta));

            messageDiv.innerHTML = `<div class="alert alert-info">Enviando...</div>`;

            try {
                const response = await fetch("salvar-documento", {
                    method: "POST",
                    body: formData
                });

                const json = await response.json();

                if (json.status === "success") {
                    messageDiv.innerHTML = `<div class="alert alert-success">${json.message}</div>`;
                    document.getElementById("topicsContainer").innerHTML = "";
                    topicCount = 0;
                    location.reload();
                } else {
                    messageDiv.innerHTML = `<div class="alert alert-danger">${json.message}</div>`;
                }

            } catch (err) {
                console.error(err);
                messageDiv.innerHTML = `<div class="alert alert-danger">Erro inesperado ao enviar. Veja console.</div>`;
            }
        });

        // -----------------------------------------------------
        // REMOVER TÓPICO NO BANCO DE DADOS
        // -----------------------------------------------------
        async function deleteTopicFromDatabase(id) {

            if (!confirm("Tem certeza que deseja excluir este tópico e todos os arquivos vinculados?"))
                return;

            try {

                const response = await fetch("excluir-topico-documento", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: `id=${encodeURIComponent(id)}`
                });

                const json = await response.json();

                if (json.status === "success") {

                    // Recarrega a página
                    location.reload();

                } else {
                    alert("Erro: " + json.message);
                }

            } catch (err) {
                console.error(err);
                alert("Erro inesperado ao tentar excluir o tópico.");
            }
        }

        // =========================
        // EDITAR TÓPICO
        // =========================
        document.addEventListener("click", function(e) {

            if (e.target.classList.contains("edit-topic")) {

                const span = e.target.previousElementSibling;
                const id = span.dataset.id;
                const valorAtual = span.innerText;

                const input = document.createElement("input");
                input.type = "text";
                input.value = valorAtual;
                input.className = "form-control";

                span.replaceWith(input);
                input.focus();

                input.addEventListener("blur", () => salvarTopico(input, id));
                input.addEventListener("keydown", (ev) => {
                    if (ev.key === "Enter") salvarTopico(input, id);
                });
            }
        });

        async function salvarTopico(input, id) {

            const novoValor = input.value.trim();

            if (!novoValor) {
                alert("Nome não pode ser vazio");
                return;
            }

            try {
                const res = await fetch("editar-topico-inline", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({
                        id: id,
                        nome: novoValor
                    })
                });

                const json = await res.json();

                if (json.status === "success") {

                    const span = document.createElement("span");
                    span.className = "editable-topic";
                    span.dataset.id = id;
                    span.innerText = novoValor;

                    input.replaceWith(span);

                } else {
                    alert(json.message);
                }

            } catch (err) {
                console.error(err);
                alert("Erro ao atualizar tópico");
            }
        }

        document.addEventListener("click", function(e) {

            if (e.target.classList.contains("edit-file")) {

                const span = e.target.previousElementSibling;
                const id = span.dataset.id;

                document.getElementById("editFileId").value = id;

                const modal = new bootstrap.Modal(document.getElementById("editFileModal"));
                modal.show();
            }
        });

        async function enviarNovoArquivo() {

            const fileInput = document.getElementById("newFileInput");
            const id = document.getElementById("editFileId").value;

            if (fileInput.files.length === 0) {
                alert("Selecione um arquivo");
                return;
            }

            const formData = new FormData();
            formData.append("id", id);
            formData.append("arquivo", fileInput.files[0]);

            try {

                const res = await fetch("editar-arquivo-inline", {
                    method: "POST",
                    body: formData
                });

                const json = await res.json();

                if (json.status === "success") {
                    location.reload();
                } else {
                    alert(json.message);
                }

            } catch (err) {
                console.error(err);
                alert("Erro ao enviar arquivo");
            }
        }

        async function deleteFileFromDatabase(fileId) {
            if (!confirm("Deseja realmente excluir este arquivo?")) return;

            try {
                const response = await fetch("remover-arquivo-doc", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: `id=${encodeURIComponent(fileId)}`
                });

                const json = await response.json();

                if (json.status === "success") {
                    // Remove o arquivo da interface sem recarregar a página
                    const li = document.querySelector(`.editable-file[data-id='${fileId}']`).closest('li');
                    if (li) li.remove();
                } else {
                    alert("Erro: " + json.message);
                }

            } catch (err) {
                console.error(err);
                alert("Erro inesperado ao tentar excluir o arquivo.");
            }
        }

        function openAddFileModal(selectedTopicId = null) {
            const topicSelect = document.getElementById("addFileTopicSelect");
            topicSelect.innerHTML = ""; // Limpa opções anteriores

            // Buscar todos os tópicos da tabela
            const topicRows = document.querySelectorAll("#topicsTable tbody tr");
            topicRows.forEach(row => {
                const span = row.querySelector(".editable-topic");
                if (!span) return;

                const id = span.dataset.id;
                const name = span.innerText;

                const option = document.createElement("option");
                option.value = id;
                option.text = name;

                // Se for o tópico clicado para adicionar arquivo, já seleciona
                if (selectedTopicId && id == selectedTopicId) {
                    option.selected = true;
                }

                topicSelect.appendChild(option);
            });

            // Resetar input de arquivo
            document.getElementById("addFileInput").value = "";

            // Mostrar modal
            const modal = new bootstrap.Modal(document.getElementById("addFileModal"));
            modal.show();
        }

        async function enviarNovoArquivoParaTopico() {
            const fileInput = document.getElementById("addFileInput");
            const topicId = document.getElementById("addFileTopicSelect").value;

            if (!fileInput.files.length) {
                alert("Selecione um arquivo.");
                return;
            }

            const formData = new FormData();
            formData.append("topico_id", topicId);
            formData.append("arquivo", fileInput.files[0]);

            try {
                const res = await fetch("adicionar-arquivo-topico", {
                    method: "POST",
                    body: formData
                });

                const json = await res.json();

                if (json.status === "success") {
                    location.reload(); // recarrega para atualizar a lista de arquivos
                } else {
                    alert(json.message);
                }

            } catch (err) {
                console.error(err);
                alert("Erro ao enviar arquivo.");
            }
        }
    </script>

    <script>
        // FILTRO DE PESQUISA (nome do tópico e datas)
        document.getElementById("searchInput").addEventListener("keyup", function() {
            let filtro = this.value.toLowerCase();
            let linhas = document.querySelectorAll("#topicsTable tbody tr");

            linhas.forEach(linha => {
                let textoLinha = linha.innerText.toLowerCase();
                linha.style.display = textoLinha.includes(filtro) ? "" : "none";
            });
        });
    </script>

</body>

</html>