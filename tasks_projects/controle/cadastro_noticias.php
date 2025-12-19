<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Notícias - EMPARN</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <!-- TinyMCE -->
    <script src="tinymce_8.2.2/tinymce/js/tinymce/tinymce.min.js"></script>
    <script src="tinymce_8.2.2/tinymce/js/tinymce/langs/pt_BR.js"></script>


    <style>
        body {
            background-color: #f4f6f8;
            display: flex;
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
        }

        .main-content {
            flex: 1;
            padding: 30px;
            margin-left: 250px;
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 20px;
            }
        }

        .card {
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
    </style>
</head>

<body>

    <!-- Menu reutilizável -->
    <?php include __DIR__ . '/../templates/gen_menu.php'; ?>
    <?php require_once __DIR__ . '/../config/connection.php';?>

    <!-- Conteúdo principal -->
    <main class="main-content">
        <h2 class="mb-4 text-primary">📰 Cadastro de Notícias</h2>

        <button id="addNewsForm" class="btn btn-success mb-4 d-flex align-items-center gap-2 shadow-sm px-3">
            <i class="bi bi-plus-circle fs-5"></i>
            <span class="fw-semibold">Nova Notícia</span>
        </button>

        <!-- Container onde os formulários aparecem -->
        <div id="newsFormsContainer"></div>
    </main>

    <footer class="bg-light text-center py-3 mt-4">
        <small>© <?= date('Y') ?> EMPARN - Painel de Controle</small>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- JS separado -->
    <script>
        const TINYMCE_COMMON = {
            license_key: 'gpl',
            height: 350,
            menubar: true,
            language: "pt_BR",

            plugins: "lists link image table code fullscreen media justify lineheight removeformat paste",

            paste_as_text: true,

            toolbar: `
                undo redo |
                bold italic underline removeformat |
                alignleft aligncenter alignright alignjustify |
                numlist bullist |
                lineheight |
                link image media table |
                fullscreen code
            `,

            branding: false,
            promotion: false,

            skin: "oxide",
            skin_url: "tinymce_8.2.2/tinymce/js/tinymce/skins/ui/oxide",

            content_css: "tinymce_8.2.2/tinymce/js/tinymce/skins/content/default/content.css",

            removeformat: [
                {
                    selector: 'b,strong,em,i,u,span,font',
                    remove: 'all'
                },
                {
                    selector: '*',
                    attributes: ['style', 'class']
                }
            ]
        };

        tinymce.init({
            selector: "textarea#meuEditor",
            ...TINYMCE_COMMON
        });


        // Inicia TinyMCE em todos os textareas com a classe .editor já existentes
        function initTinyMCE() {
            // Evita inicializar novamente editores já inicializados
            document.querySelectorAll("textarea.editor").forEach(txt => {
                if (!tinymce.get(txt.id)) {
                    // se não tiver id, cria um
                    if (!txt.id) txt.id = "mce_" + Math.random().toString(36).slice(2, 9);
                    tinymce.init(Object.assign({}, TINYMCE_COMMON, { target: txt }));
                }
            });
        }

        // Inicia TinyMCE apenas em um textarea DOM (usado ao criar tópico dinamicamente)
        function initTinyMCEOnElement(textareaEl) {
            if (!textareaEl.id) textareaEl.id = "mce_" + Math.random().toString(36).slice(2, 9);
            tinymce.init(Object.assign({}, TINYMCE_COMMON, { target: textareaEl }));
        }

        // Garante que conteúdo do TinyMCE vai para os <textarea> antes de ler o form
        function triggerTinySaveAll() {
            if (typeof tinymce !== "undefined") tinymce.triggerSave();
        }

        // ====================== UTIL: RENOMEAR TÓPICOS (names & data-file-name) ======================
        function renumerarTopicos(container, formId) {
            const topicos = container.querySelectorAll(".topico");

            topicos.forEach((topico, index) => {
                const header = topico.querySelector("h5");
                if (header) header.innerText = `Tópico ${index + 1}`;

                topico.querySelectorAll("input, textarea, select").forEach(el => {
                    const oldName = el.getAttribute("name");
                    if (!oldName) return;
                    const newName = oldName.replace(/topicos\[\d+\]/, `topicos[${index}]`);
                    el.setAttribute("name", newName);
                });

                // ajustar input file dataset/name
                const fileInput = topico.querySelector(".topico-file");
                if (fileInput) {
                    fileInput.setAttribute("name", `topicos[${index}][imagem]`);
                    fileInput.dataset.fileName = `topicos_${index}_imagem`;
                }
            });
        }

        // ====================== DND NATIVE (drag & drop simples) ======================
        // Ativa drag nativo em todos os .topico dentro do container
        function enableDragForContainer(container, formId) {
            let dragSrcEl = null;

            container.querySelectorAll(".topico").forEach(item => {
                item.setAttribute("draggable", "true");
                // add handlers
                item.addEventListener("dragstart", (e) => {
                    triggerTinySaveAll(); // salva conteúdo para evitar perda ao rearranjar
                    dragSrcEl = item;
                    item.classList.add("dragging");
                    e.dataTransfer.effectAllowed = "move";
                    try { e.dataTransfer.setData("text/plain", "dragging"); } catch (err) { /* firefox */ }
                });

                item.addEventListener("dragend", () => {
                    item.classList.remove("dragging");
                    dragSrcEl = null;
                });
            });

            // while dragging over container, determine position
            container.addEventListener("dragover", (e) => {
                e.preventDefault();
                const afterElement = getDragAfterElement(container, e.clientY);
                const dragging = container.querySelector(".dragging");
                if (!dragging) return;
                if (!afterElement) {
                    container.appendChild(dragging);
                } else {
                    container.insertBefore(dragging, afterElement);
                }
            });

            // on drop, renumerar
            container.addEventListener("drop", (e) => {
                e.preventDefault();
                renumerarTopicos(container, formId);
            });
        }

        // helper: find element after cursor
        function getDragAfterElement(container, y) {
            const draggableElements = [...container.querySelectorAll('.topico:not(.dragging)')];

            return draggableElements.reduce((closest, child) => {
                const box = child.getBoundingClientRect();
                const offset = y - box.top - box.height / 2;
                // offset negative means cursor is above center -> candidate
                if (offset < 0 && offset > closest.offset) {
                    return { offset: offset, element: child };
                } else {
                    return closest;
                }
            }, { offset: Number.NEGATIVE_INFINITY }).element;
        }

        // ====================== ADICIONAR TÓPICO ======================
        function addTopic(formId) {
            const container = document.querySelector(`#topicos-container-${formId}`);
            if (!container) return console.warn("Container de tópicos não encontrado:", formId);

            const index = container.children.length;

            const topicoHTML = document.createElement("div");
            topicoHTML.className = "topico border rounded p-3 mb-3";
            topicoHTML.setAttribute("data-index", index);
            topicoHTML.setAttribute("draggable", "true");

            topicoHTML.innerHTML = `
                <input type="hidden" name="topicos[${index}][id]" value="">

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h5>Tópico ${index + 1}</h5>
                    <div>
                        <button type="button" class="btn btn-sm btn-outline-danger me-1" onclick="removeTopic(this, ${formId})">
                            <i class="bi bi-x-circle"></i> Remover
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary drag-handle" title="Arrastar">
                            <i class="bi bi-arrows-move"></i>
                        </button>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Imagem:</label>
                    <input type="file" class="form-control topico-file" accept="image/*" data-file-name="topicos_${index}_imagem">
                    <div class="small text-muted mt-1">Tamanho recomendado: 1200x800</div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Fonte da Imagem:</label>
                    <textarea name="topicos[${index}][fonte_imagem]" class="form-control"></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Título do Tópico:</label>
                    <input type="text" name="topicos[${index}][titulo]" class="form-control">
                </div>

                <div class="mb-3">
                    <label class="form-label">Texto:</label>
                    <textarea name="topicos[${index}][texto]" class="form-control editor" rows="5"></textarea>
                </div>
            `;

            container.appendChild(topicoHTML);

            // set proper name attribute for file (for PHP $_FILES)
            const fileInput = topicoHTML.querySelector(".topico-file");
            if (fileInput) {
                fileInput.name = `topicos[${index}][imagem]`;
                fileInput.dataset.fileName = `topicos_${index}_imagem`;
            }

            // init TinyMCE only on the new textarea
            const textarea = topicoHTML.querySelector("textarea.editor");
            if (textarea) initTinyMCEOnElement(textarea);

            // attach drag handlers for the new element
            attachDragHandlersToTopico(topicoHTML, container, formId);

            // renumerar para garantir nomes corretos (ótimo quando removidos/ordenados)
            renumerarTopicos(container, formId);
        }

        // attach drag handlers to a specific topico element
        function attachDragHandlersToTopico(topicoEl, container, formId) {
            topicoEl.addEventListener("dragstart", (e) => {
                triggerTinySaveAll();
                topicoEl.classList.add("dragging");
                try { e.dataTransfer.setData("text/plain", "drag"); } catch (err) {}
            });
            topicoEl.addEventListener("dragend", () => {
                topicoEl.classList.remove("dragging");
                renumerarTopicos(container, formId);
            });
        }

        // ====================== REMOVER TÓPICO ======================
        function removeTopic(btn, formId) {
            if (!confirm("Tem certeza que deseja remover este tópico?")) return;

            const topicoEl = btn.closest(".topico");
            const container = document.querySelector(`#topicos-container-${formId}`);
            if (!topicoEl || !container) return;

            // remove editor instance for the textarea inside (to avoid orphan instances)
            const ta = topicoEl.querySelector("textarea");
            if (ta && ta.id && tinymce.get(ta.id)) {
                tinymce.get(ta.id).remove();
            }

            topicoEl.remove();
            renumerarTopicos(container, formId);
        }

        // ====================== CRIAR FORMULÁRIO DE NOTÍCIA (BOTÃO) ======================
        let formCount = 0;

        document.addEventListener("DOMContentLoaded", () => {
            const addNewsBtn = document.getElementById("addNewsForm");
            if (!addNewsBtn) {
                console.warn("Botão addNewsForm não encontrado.");
                return;
            }

            addNewsBtn.addEventListener("click", () => {
                formCount++;
                const formId = formCount;

                const formHTML = `
                    <div class="card mb-4" id="news-card-${formId}">
                        <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-newspaper"></i> Nova Notícia #${formId}</span>
                            <button type="button" class="btn btn-sm btn-outline-light" onclick="removeNews(${formId})">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>

                        <div class="card-body">
                            <form class="newsForm" enctype="multipart/form-data">

                                <input type="hidden" name="id" value="">

                                <div class="mb-3">
                                    <label class="form-label">Título:</label>
                                    <input type="text" name="titulo" class="form-control" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Subtítulo:</label>
                                    <input type="text" name="subtitulo" class="form-control">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Autoria:</label>
                                    <input type="text" name="autoria" class="form-control" required>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Imagem Principal:</label>
                                        <input type="file" name="imagem" class="form-control" accept="image/*">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Fonte da Imagem Principal:</label>
                                        <input type="text" name="fonte_imagem" class="form-control" required>
                                    </div>
                                </div>

                                <div class="mb-3 mt-3">
                                    <label class="form-label">Conteúdo Principal:</label>
                                    <textarea name="texto" class="form-control editor" rows="6" required></textarea>
                                </div>

                                <hr>
                                <h5>Tópicos</h5>
                                <div id="topicos-container-${formId}" class="topicos-container"></div>

                                <div class="d-flex gap-3 mt-3">
                                    <button type="button" class="btn btn-outline-secondary" onclick="addTopic(${formId})">
                                        <i class="bi bi-plus-circle"></i> Adicionar Tópico
                                    </button>

                                    <button type="button" class="btn btn-danger save-btn">
                                        <i class="bi bi-save"></i> <span>Salvar Notícia</span>
                                    </button>
                                </div>

                            </form>
                        </div>
                    </div>
                `;

                document.getElementById("newsFormsContainer").insertAdjacentHTML("beforeend", formHTML);

                // inicializa editor do conteúdo principal (apenas esse)
                const card = document.getElementById(`news-card-${formId}`);
                const mainTextarea = card.querySelector("textarea[name='texto']");
                if (mainTextarea) initTinyMCEOnElement(mainTextarea);

                // cria primeiro tópico automaticamente
                addTopic(formId);

                // habilita drag & drop no container
                const container = document.querySelector(`#topicos-container-${formId}`);
                enableDragForContainer(container, formId);
            });
        });

        // ====================== PRIMEIRO TÓPICO (CRIA) ======================
        // removido: criarTopicoInicial() usa addTopic(formId) now

        // ====================== REMOVER NOTÍCIA (CARD) ======================
        function removeNews(formId) {
            if (!confirm("Tem certeza que deseja excluir esta notícia inteira?")) return;
            const card = document.getElementById(`news-card-${formId}`);
            if (card) {
                // removendo editores TinyMCE associados dentro do card
                card.querySelectorAll("textarea").forEach(t => {
                    if (t.id && tinymce.get(t.id)) tinymce.get(t.id).remove();
                });
                card.remove();
            }
        }

        // ====================== SALVAR NOTÍCIA (API) ======================
        async function salvarNoticia(form) {
            triggerTinySaveAll();
            const fd = new FormData(form);

            // anexa arquivos dos tópicos
            form.querySelectorAll(".topico-file").forEach(input => {
                const dataName = input.dataset.fileName;
                if (input.files.length > 0) {
                    fd.append(dataName, input.files[0]);
                }
            });

            try {
                const response = await fetch("criar-nova-noticia", {
                    method: "POST",
                    body: fd
                });

                const result = await response.json();

                if (result.status === "success") {
                    alert(`Notícia "${result.titulo}" salva com sucesso!`);

                    window.location.href = "view-noticias-existentes";

                } else {
                    alert("Erro: " + (result.message || "Erro desconhecido"));
                }

            } catch (err) {
                alert("Erro ao enviar: " + err.message);
            }
        }

        // delega clique no botão salvar (funciona para formulários dinâmicos)
        document.addEventListener("click", (e) => {
            const btn = e.target.closest(".save-btn");
            if (!btn) return;
            const form = btn.closest(".newsForm");
            if (!form) return;
            salvarNoticia(form);
        });

    </script>

</body>
</html>
