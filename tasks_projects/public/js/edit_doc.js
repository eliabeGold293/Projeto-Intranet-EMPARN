/*  JS para edição multi-tópicos
    - Usa UUIDs para novos tópicos (estáveis no front)
    - Para tópicos existentes usamos prefix 'db_<id>' para associá-los (mantemos referência ao banco)
    - Arquivos novos por tópico virão como files_topic_<key>[]
*/

let topicCounter = 0;

// Utility: criar ID de cliente (uuid simples fallback)
function makeUUID() {
    if (crypto && crypto.randomUUID) return crypto.randomUUID();
    // fallback simples (não-criogr.)
    return 'c' + Date.now().toString(36) + Math.random().toString(36).slice(2,8);
}

// monta um bloco de tópico. key pode ser "db_123" (tópico existente) ou uuid do cliente
function createTopicBlock({ key, nome = '', descricao = '', arquivos = [] }) {
    topicCounter++;

    const div = document.createElement('div');
    div.className = 'topic-form';
    div.dataset.topicKey = key;

    div.innerHTML = `
        <span class="remove-topic text-danger fw-bold" title="Remover tópico" style="cursor:pointer">
            <i class="bi bi-x-lg"></i>
        </span>

        <h5 class="mb-2 topic-title">Tópico ${topicCounter}</h5>

        <div class="mb-2">
            <label class="form-label">Nome do Tópico</label>
            <input type="text" class="form-control topic-name" value="${escapeHtml(nome)}" required>
        </div>

        <div class="mb-2">
            <label class="form-label">Descrição (opcional)</label>
            <textarea class="form-control topic-desc" rows="2">${escapeHtml(descricao)}</textarea>
        </div>

        <div class="existing-files mb-2"></div>

        <div class="files-container mb-2"></div>

        <div class="d-flex gap-2 mt-2">
            <button type="button" class="btn btn-outline-primary btn-sm add-file-btn">
                <i class="bi bi-paperclip"></i> Adicionar Arquivo
            </button>
            <small class="text-muted align-self-center">Arraste e solte não implementado aqui</small>
        </div>
    `;

    // remover tópico
    div.querySelector('.remove-topic').addEventListener('click', () => {
        if (!confirm('Remover este tópico (isso não excluirá arquivos do servidor automaticamente)?')) return;
        div.remove();
        reorderDisplayNumbers();
    });

    // adicionar arquivo
    div.querySelector('.add-file-btn').addEventListener('click', () => {
        addFileInputToBlock(div);
    });

    // preencher arquivos existentes (apenas se houver)
    const existingContainer = div.querySelector('.existing-files');
    if (Array.isArray(arquivos) && arquivos.length > 0) {
        arquivos.forEach(arq => {
            const row = document.createElement('div');
            row.className = 'existing-file';
            row.dataset.fileId = 'dbfile_' + arq.id; // identificador DOM

            row.innerHTML = `
                <div><i class="bi bi-file-earmark me-2"></i> ${escapeHtml(arq.nome_original)}
                    <div class="small text-muted">(${escapeHtml(arq.tipo)} • ${Math.round(arq.tamanho/1024)} KB)</div>
                </div>
                <div>
                    <button type="button" class="btn btn-sm btn-danger remove-existing-file" data-file-db-id="${arq.id}">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            `;
            function debugDownloadPath(el) {
                console.log("Download path:", el.href);
            }


            existingContainer.appendChild(row);
        });
    }

    return div;
}

// escape simples para inserir valores em HTML
function escapeHtml(str) {
    if (str === null || str === undefined) return '';
    return String(str)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

// adicionar input file no bloco
function addFileInputToBlock(block) {
    const fileContainer = block.querySelector('.files-container');
    const row = document.createElement('div');
    row.className = 'file-row';

    row.innerHTML = `
        <input type="file" class="form-control file-input">
        <button type="button" class="btn btn-danger btn-sm btn-remove-file"><i class="bi bi-x-lg"></i></button>
    `;
    // remover linha
    row.querySelector('.btn-remove-file').addEventListener('click', () => row.remove());
    fileContainer.appendChild(row);
}

// reordena apenas os números visuais "Tópico 1, 2..."
function reorderDisplayNumbers() {
    const topics = document.querySelectorAll('.topic-form');
    let n = 1;
    topics.forEach(t => {
        const title = t.querySelector('.topic-title');
        if (title) title.textContent = `Tópico ${n}`;
        n++;
    });
    topicCounter = topics.length;
}

// carregar tópicos existentes do servidor (injetados em existingTopics)
function loadExistingTopicsArray(arr) {
    if (!Array.isArray(arr)) return;
    const container = document.getElementById('topicsContainer');

    // preferimos inserir tópicos existentes primeiro (você pode escolher a ordem)
    arr.forEach(t => {
        // use key = 'db_<id>' para tópicos que existem no DB
        const key = 'db_' + t.id;
        const block = createTopicBlock({ key, nome: t.nome, descricao: t.descricao ?? '', arquivos: t.arquivos ?? [] });
        block.dataset.dbId = t.id; // marca DOM com id do DB quando aplicável
        container.appendChild(block);
    });

    // adicionar evento remover arquivo existente (delegação)
    container.addEventListener('click', function (ev) {
        const btn = ev.target.closest('.remove-existing-file');
        if (!btn) return;
        const fileDbId = btn.dataset.fileDbId;
        if (!fileDbId) return;
        removeExistingFile(fileDbId, btn);
    });

    reorderDisplayNumbers();
}

// remove arquivo existente via API
async function removeExistingFile(fileDbId, btnElement) {
    if (!confirm('Remover este arquivo permanentemente?')) return;
    try {
        const res = await fetch('../apis/remover_arquivo.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id=${encodeURIComponent(fileDbId)}`
        });
        const json = await res.json();
        if (json.status === 'success') {
            // remove da UI
            const row = btnElement.closest('[data-file-id]');
            if (row) row.remove();
        } else {
            alert('Erro: ' + (json.message || 'Não foi possível remover.'));
        }
    } catch (e) {
        console.error(e);
        alert('Erro inesperado ao remover arquivo.');
    }
}

// criar novo tópico vazio (cliente)
function addNewClientTopic() {
    const key = makeUUID();
    const block = createTopicBlock({ key, nome: '', descricao: '', arquivos: [] });
    document.getElementById('topicsContainer').appendChild(block);
    reorderDisplayNumbers();
    // abrir foco no nome
    setTimeout(() => block.querySelector('.topic-name')?.focus(), 60);
}

// salvar tudo (metadados + arquivos novos) via FormData
async function saveAllTopics() {
    if (!confirm('Deseja realmente salvar todas as alterações?')) return;

    const messageDiv = document.getElementById('message');
    messageDiv.innerHTML = '';

    const topics = document.querySelectorAll('.topic-form');
    if (topics.length === 0) {
        messageDiv.innerHTML = `<div class="alert alert-warning">Nenhum tópico para salvar.</div>`;
        return;
    }

    const formData = new FormData();
    const meta = [];

    // percorre tópicos e monta metadados + arquivos por key
    for (let i = 0; i < topics.length; i++) {
        const t = topics[i];
        const key = t.dataset.topicKey || makeUUID();
        const dbId = t.dataset.dbId ? t.dataset.dbId : null; // se for db_<id>, mantemos referência
        const nome = t.querySelector('.topic-name')?.value.trim() ?? '';
        const descricao = t.querySelector('.topic-desc')?.value.trim() ?? '';

        if (!nome) {
            messageDiv.innerHTML = `<div class="alert alert-warning">O tópico ${i+1} precisa ter um nome.</div>`;
            return;
        }

        meta.push({ key, dbId, nome, descricao });

        // arquivos novos
        const fileInputs = t.querySelectorAll('.file-input');
        fileInputs.forEach((fi) => {
            if (fi.files && fi.files.length > 0) {
                for (let k = 0; k < fi.files.length; k++) {
                    // campo agrupado por key -> files_topic_<key>[]
                    formData.append(`files_topic_${key}[]`, fi.files[k]);
                }
            }
        });
    }

    formData.append('topics_meta', JSON.stringify(meta));
    formData.append('action', 'editar_multiplo');

    messageDiv.innerHTML = `<div class="alert alert-info">Enviando. Aguarde...</div>`;

    try {
        const res = await fetch('../apis/salvar_edicao_topicos.php', {
            method: 'POST',
            body: formData
        });

        const json = await res.json();
        if (json.status === 'success') {
            messageDiv.innerHTML = `<div class="alert alert-success">${json.message}</div>`;
            // opcional: recarregar para atualizar dados reais do DB (ou atualizar partes da UI)
            setTimeout(() => location.reload(), 800);
        } else {
            messageDiv.innerHTML = `<div class="alert alert-danger">${json.message || 'Erro no servidor'}</div>`;
        }
    } catch (e) {
        console.error(e);
        messageDiv.innerHTML = `<div class="alert alert-danger">Erro inesperado ao enviar. Veja console.</div>`;
    }
}

// inicialização
document.addEventListener('DOMContentLoaded', () => {
    // carregar tópicos do servidor (variável injected)
    if (Array.isArray(existingTopics) && existingTopics.length > 0) {
        loadExistingTopicsArray(existingTopics);
    }

    // eventos globais
    document.getElementById('addTopicBtn').addEventListener('click', addNewClientTopic);
    document.getElementById('saveAllBtn').addEventListener('click', saveAllTopics);
});