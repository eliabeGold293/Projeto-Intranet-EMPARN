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
            <i class="bi bi-paperclip"></i> Adicionar Arquivo
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
        fileInputs.forEach(fi => { if (fi.files.length > 0) hasFile = true; });

        if (!hasFile) {
            messageDiv.innerHTML = `<div class="alert alert-danger">O tópico ${index + 1} precisa ter ao menos um arquivo anexado.</div>`;
            return;
        }

        // Monta metadados
        topicsMeta.push({ nome, descricao });

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
        const response = await fetch("../apis/salvar_topicos.php", {
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

        const response = await fetch("../apis/excluir_topico.php", {
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
