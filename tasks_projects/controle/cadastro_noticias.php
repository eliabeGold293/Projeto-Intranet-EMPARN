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

        <div id="local-button">
            <button id="addNewsForm" onclick="addNoticia()" class="btn btn-success mb-4 d-flex align-items-center gap-2 shadow-sm px-3">
            <i class="bi bi-plus-circle fs-5"></i>
            <span class="fw-semibold">Nova Notícia</span>
        </button>

        </div>
        
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

        function addNoticia(){

            let formId = 1;

            const divNoticia = document.getElementById("newsFormsContainer");
            const botaoNewForm = document.getElementById("addNewsForm");

            botaoNewForm.remove();

            divNoticia.innerHTML = `

                <div class="card mb-4" id="news-card-${formId}">
                    <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-newspaper"></i> Nova Notícia</span>
                        <button type="button" class="btn btn-sm btn-outline-light" onclick="removeNews()">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>

                    <div class="card-body">
                        <form class="newsForm" id="form" enctype="multipart/form-data">

                            <input type="hidden" name="id" value="" required>

                            <div class="mb-3">
                                <label class="form-label">Título:</label>
                                <input type="text" name="titulo" class="form-control" required required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Subtítulo:</label>
                                <input type="text" name="subtitulo" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Autoria:</label>
                                <input type="text" name="autoria" class="form-control" required>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Imagem Principal:</label>
                                    <input type="file" name="imagem" class="form-control" accept="image/*" required>
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
                            <div id="topicos-container" class="topicos-container"></div>

                            <div class="d-flex gap-3 mt-3">
                                <button type="button" class="btn btn-outline-secondary" onclick="gerarTopico()">
                                    <i class="bi bi-plus-circle"></i> Adicionar Tópico
                                </button>

                                <button type="button" onclick="salvarNoticia()" class="btn btn-danger save-btn">
                                    <i class="bi bi-save"></i> <span>Salvar Notícia</span>
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
            `;

            initTinyMCE();
        }

        let topicoIndex = 0;
        
        function gerarTopico() {
            const topicosContainer = document.getElementById("topicos-container");
            const index = topicoIndex++;

            const div = document.createElement("div");
            div.classList.add("border", "rounded", "p-3", "mb-3");

            div.innerHTML = `
                <input type="hidden" name="topicos[${index}][id]" value="">

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h5 class="topico-titulo">Tópico</h5>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeTopico(this)">
                        <i class="bi bi-x-circle"></i> Remover
                    </button>
                </div>

                <div class="mb-3">
                    <label class="form-label">Imagem:</label>
                    <input type="file" id="file"
                        name="topicos[${index}][imagem]"
                        class="form-control topico-file"
                        accept="image/*"
                        required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Fonte da Imagem:</label>
                    <input type="text" id="fonte"
                        name="topicos[${index}][fonte_imagem]"
                        class="form-control"
                        required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Título do Tópico:</label>
                    <input type="text" id="titulo-topico"
                        name="topicos[${index}][titulo]"
                        class="form-control"
                        required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Texto:</label>
                    <textarea name="topicos[${index}][texto]"
                            id="texto-topico"
                            class="form-control editor"
                            rows="5"
                            required></textarea>
                </div>
            `;

            topicosContainer.appendChild(div);
            initTinyMCE();
            atualizarNumeracaoTopicos();
        }

        function atualizarNumeracaoTopicos() {
            document.querySelectorAll(".topicos-container > div").forEach((topico, i) => {
                const titulo = topico.querySelector(".topico-titulo");
                if (titulo) {
                    titulo.textContent = `Tópico ${i + 1}`;
                }
            });
        }

        function removeNews() {

            const card = document.getElementById("news-card-1");
            const localBotao = document.getElementById("local-button");

            if(confirm("Tem certeza que deseja deletar toda a notícia?")){
                
                if (card) card.remove();

                localBotao.innerHTML = `
                    <button id="addNewsForm" onclick="addNoticia()"
                        class="btn btn-success mb-4 d-flex align-items-center gap-2 shadow-sm px-3">
                        <i class="bi bi-plus-circle fs-5"></i>
                        <span class="fw-semibold">Nova Notícia</span>
                    </button>
                `;

            }
        }

        function removeTopico(btn){

            if (confirm("Tem certeza que deseja remover o tópico?")) {

                btn.closest(".border").remove();
                atualizarNumeracaoTopicos();
            }
        }


        async function salvarNoticia() {

            if(confirm("Tem certeza que deseja salvar esta notícia?")){

                const topicosContainer = document.getElementById("topicos-container");

                // valida tópicos
                const topicos = document.querySelectorAll(".topicos-container > div");

                for (let i = 0; i < topicos.length; i++) {
                    const topico = topicos[i];

                    const imagem = topico.querySelector('input[type="file"]');
                    const fonteImagem = topico.querySelector('input[name$="[fonte_imagem]"]');
                    const titulo = topico.querySelector('input[name$="[titulo]"]');
                    const texto = topico.querySelector('textarea[name$="[texto]"]');

                    if (
                        !imagem.files.length ||
                        fonteImagem.value.trim() === "" ||
                        titulo.value.trim() === "" ||
                        texto.value.trim() === ""
                    ) {
                        alert(`Preencha TODOS os campos do(os) Tópico(os).`);
                        return;
                    }
                }

                triggerTinySaveAll();
                const form = document.getElementById("form");
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

            } else{
                alert("Ok!");
            }
        }
    </script>
</body>
</html>
