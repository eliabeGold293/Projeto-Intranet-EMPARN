<?php
// editar_noticia.php
require_once "../config/connection.php";

$id = isset($_GET['id']) ? intval($_GET['id']) : null;
if (!$id) {
    die("Notícia não encontrada (id ausente).");
}

// Carrega notícia
$stmt = $pdo->prepare("SELECT * FROM noticias WHERE id = ?");
$stmt->execute([$id]);
$noticia = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$noticia) die("Notícia não encontrada.");

// Carrega tópicos
$stmtT = $pdo->prepare("SELECT * FROM noticia_topicos WHERE noticia_id = ? ORDER BY ordem ASC");
$stmtT->execute([$id]);
$topicos = $stmtT->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Editar Notícia — <?= htmlspecialchars($noticia['titulo']) ?></title>

<!-- Bootstrap + Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<!-- TinyMCE -->
<script src="../tinymce_8.2.2/tinymce/js/tinymce/tinymce.min.js"></script>
<script src="../tinymce_8.2.2/tinymce/js/tinymce/langs/pt_BR.js"></script>

<style>
    /* layout geral */
    body { background:#f5f7f9; font-family: "Segoe UI", Arial, sans-serif; }

    /* container usado pelo seu formulário */
    .container-main {
        margin-left: 250px; /* mesmo deslocamento do menu lateral */
        padding: 28px;
        max-width: 1100px;
    }

    /* pequena proteção se outro CSS setar margin-left:0 antes */
    .main-content { margin-left: 250px; padding: 20px; }

    /* mobile: remove margem para telas pequenas */
    @media (max-width: 768px) {
        .container-main,
        .main-content {
            margin-left: 0;
            padding-left: 16px;
            padding-right: 16px;
        }
    }

    /* outros estilos visuais */
    .card-noticia { border-radius: .75rem; }
    .topico { cursor: grab; }
    .topico.dragging { opacity: .6; }
    .img-preview { max-height: 360px; width: 100%; object-fit: cover; border-radius: .5rem; }
    .small-muted { font-size:.85rem; color:#6c757d; }
</style>

</head>
<body>

<!-- menu lateral reutilizável se desejar -->
<?php if (file_exists(__DIR__ . '/../templates/gen_menu.php')) include __DIR__ . '/../templates/gen_menu.php'; ?>

<div class="container container-main">

    <div class="d-flex justify-content-between align-items-start mb-3">
        <h3 class="mb-0">Editar Notícia — ID <?= $noticia['id'] ?></h3>
        <div class="d-flex gap-2">
            <a class="btn btn-danger"
               href="../apis/deletar_noticias.php?id=<?= $noticia['id'] ?>"
               onclick="return confirm('Excluir essa notícia definitivamente?');">
               <i class="bi bi-trash"></i> Deletar notícia
            </a>
        </div>
    </div>

    <div class="card card-noticia shadow-sm mb-4">
        <div class="card-body">

            <!-- FORMULÁRIO (preenchido para edição) -->
            <form id="editNewsForm" class="newsForm" enctype="multipart/form-data">

                <input type="hidden" name="id" value="<?= $noticia['id'] ?>">

                <div class="row g-3">
                    <div class="col-12 col-lg-8">
                        <div class="mb-3">
                            <label class="form-label">Título</label>
                            <input type="text" name="titulo" class="form-control" required value="<?= htmlspecialchars($noticia['titulo']) ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Subtítulo</label>
                            <input type="text" name="subtitulo" class="form-control" value="<?= htmlspecialchars($noticia['subtitulo']) ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Autoria</label>
                            <input type="text" name="autoria" class="form-control" required value="<?= htmlspecialchars($noticia['autoria']) ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Conteúdo Principal</label>
                            <textarea name="texto" class="form-control editor" rows="10" required><?= $noticia['texto'] ?></textarea>
                        </div>
                    </div>

                    <div class="col-12 col-lg-4">

                        <div class="mb-3">
                            <label class="form-label">Imagem Principal (substituir)</label>
                            <?php if ($noticia['imagem']): ?>
                                <div class="mb-2">
                                    <img src="../<?= htmlspecialchars($noticia['imagem']) ?>" class="img-preview mb-2" alt="Imagem principal">
                                    <p class="small-muted mb-2">Atual: <?= htmlspecialchars($noticia['imagem']) ?></p>
                                </div>
                            <?php endif; ?>
                            <input type="file" name="imagem" class="form-control" accept="image/*">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Fonte da Imagem Principal</label>
                            <textarea name="fonte_imagem" class="form-control"><?= htmlspecialchars($noticia['fonte_imagem']) ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Link (opcional)</label>
                            <input type="text" name="link" class="form-control" value="<?= htmlspecialchars($noticia['link']) ?>">
                            <small class="small-muted">Se preenchido, será usado como link externo.</small>
                        </div>

                        <div class="d-grid mt-4">
                            <button type="button" class="btn btn-success save-btn">
                                <i class="bi bi-save"></i> Salvar alterações
                            </button>
                        </div>

                    </div>
                    
                </div>

                <hr class="my-4">

                <h5>Tópicos</h5>
                <p class="small-muted">Arraste para reordenar. Adicione, edite imagens e conteúdo.</p>

                <div id="topicos-container-<?= $noticia['id'] ?>" class="topicos-container mb-3">
                    <!-- tópicos existentes -->
                    <?php
                    $i = 0;
                    foreach ($topicos as $t):
                        $tid = intval($t['id']);
                        $ttitulo = htmlspecialchars($t['titulo']);
                        $ttexto = $t['texto'];
                        $timagem = htmlspecialchars($t['imagem']);
                        $tfonte = htmlspecialchars($t['fonte_imagem']);
                    ?>
                    <div class="topico border rounded p-3 mb-3" data-index="<?= $i ?>" draggable="true">
                        <input type="hidden" name="topicos[<?= $i ?>][id]" value="<?= $tid ?>">

                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h5>Tópico <?= $i + 1 ?></h5>
                            <div>
                                <button type="button" class="btn btn-sm btn-outline-danger me-1"
                                        onclick="removeTopic(this, <?= $noticia['id'] ?>)">
                                    <i class="bi bi-x-circle"></i> Remover
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary drag-handle" title="Arrastar">
                                    <i class="bi bi-arrows-move"></i>
                                </button>
                            </div>
                        </div>

                        <?php if ($timagem): ?>
                            <div class="mb-2">
                                <img src="../<?= $timagem ?>" class="img-preview mb-2">
                                <p class="small-muted mb-2">Atual: <?= $timagem ?></p>
                            </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label class="form-label">Imagem (substituir)</label>
                            <input type="file" class="form-control topico-file" accept="image/*"
                                   data-file-name="topicos_<?= $i ?>_imagem">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Fonte da Imagem</label>
                            <textarea name="topicos[<?= $i ?>][fonte_imagem]" class="form-control"><?= $tfonte ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Título do Tópico</label>
                            <input type="text" name="topicos[<?= $i ?>][titulo]" class="form-control" value="<?= $ttitulo ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Texto</label>
                            <textarea name="topicos[<?= $i ?>][texto]" class="form-control editor" rows="5"><?= $ttexto ?></textarea>
                        </div>
                    </div>
                    <?php
                        $i++;
                    endforeach;
                    ?>
                </div>

                <div class="d-flex gap-2 mb-4">
                    <button type="button" class="btn btn-outline-secondary" onclick="addTopic(<?= $noticia['id'] ?>)">
                        <i class="bi bi-plus-circle"></i> Adicionar tópico
                    </button>
                    <button type="button" class="btn btn-outline-danger" onclick="removeAllTopics(<?= $noticia['id'] ?>)">
                        <i class="bi bi-trash"></i> Remover Todos os Tópicos
                    </button>
                </div>

            </form>
        </div>
    </div>

</div>

<script>
const TINYMCE_COMMON = {
    license_key: 'gpl',
    height: 350,
    menubar: true,
    language: "pt_BR",

    plugins: "lists link image table code fullscreen media justify lineheight",

    toolbar: `
        undo redo |
        bold italic underline |
        alignleft aligncenter alignright alignjustify |
        numlist bullist |
        lineheight |
        link image media table |
        fullscreen code
    `,

    branding: false,
    promotion: false,

    skin: "oxide",
    skin_url: "../tinymce_8.2.2/tinymce/js/tinymce/skins/ui/oxide",

    content_css: "../tinymce_8.2.2/tinymce/js/tinymce/skins/content/default/content.css",
};

tinymce.init({
    selector: "textarea#meuEditor",
    ...TINYMCE_COMMON
});

function initTinyMCE() {
    document.querySelectorAll("textarea.editor").forEach(txt => {
        // evita re-inicializar
        if (txt.id && tinymce.get(txt.id)) return;
        if (!txt.id) txt.id = "mce_" + Math.random().toString(36).slice(2, 9);
        tinymce.init(Object.assign({}, TINYMCE_COMMON, { target: txt }));
    });
}

function initTinyMCEOnElement(textareaEl) {
    if (textareaEl.id && tinymce.get(textareaEl.id)) return;
    if (!textareaEl.id) textareaEl.id = "mce_" + Math.random().toString(36).slice(2, 9);
    tinymce.init(Object.assign({}, TINYMCE_COMMON, { target: textareaEl }));
}

function triggerTinySaveAll() {
    if (typeof tinymce !== "undefined") tinymce.triggerSave();
}

/* ====================== RENOMEAR TÓPICOS (names & data-file-name) ====================== */
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

        const fileInput = topico.querySelector(".topico-file");
        if (fileInput) {
            fileInput.setAttribute("name", `topicos[${index}][imagem]`);
            fileInput.dataset.fileName = `topicos_${index}_imagem`;
        }
    });
}

/* ====================== DND NATIVE ====================== */
function enableDragForContainer(container, formId) {
    container.querySelectorAll(".topico").forEach(item => {
        item.setAttribute("draggable", "true");
        item.addEventListener("dragstart", (e) => {
            triggerTinySaveAll();
            item.classList.add("dragging");
            try { e.dataTransfer.setData("text/plain", "drag"); } catch (err) {}
        });
        item.addEventListener("dragend", () => {
            item.classList.remove("dragging");
            renumerarTopicos(container, formId);
        });
    });

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

    container.addEventListener("drop", (e) => {
        e.preventDefault();
        renumerarTopicos(container, formId);
    });
}

function getDragAfterElement(container, y) {
    const draggableElements = [...container.querySelectorAll('.topico:not(.dragging)')];
    return draggableElements.reduce((closest, child) => {
        const box = child.getBoundingClientRect();
        const offset = y - box.top - box.height / 2;
        if (offset < 0 && offset > closest.offset) {
            return { offset: offset, element: child };
        } else {
            return closest;
        }
    }, { offset: Number.NEGATIVE_INFINITY }).element;
}

/* ====================== ADICIONAR TÓPICO DINÂMICO ====================== */
function addTopic(formId) {
    const container = document.querySelector(`#topicos-container-${formId}`);
    if (!container) return;

    const index = container.children.length;

    const div = document.createElement("div");
    div.className = "topico border rounded p-3 mb-3";
    div.setAttribute("draggable", "true");
    div.innerHTML = `
        <input type="hidden" name="topicos[${index}][id]" value="">

        <div class="d-flex justify-content-between align-items-center mb-2">
            <h5>Tópico ${index + 1}</h5>
            <div>
                <button type="button" class="btn btn-sm btn-outline-danger me-1" onclick="removeTopic(this, ${formId})">
                    <i class="bi bi-x-circle"></i> Remover
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary drag-handle">
                    <i class="bi bi-arrows-move"></i>
                </button>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Imagem</label>
            <input type="file" class="form-control topico-file" accept="image/*" data-file-name="topicos_${index}_imagem">
        </div>

        <div class="mb-3">
            <label class="form-label">Fonte da Imagem</label>
            <textarea name="topicos[${index}][fonte_imagem]" class="form-control"></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Título do Tópico</label>
            <input type="text" name="topicos[${index}][titulo]" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Texto</label>
            <textarea name="topicos[${index}][texto]" class="form-control editor" rows="5"></textarea>
        </div>
    `;

    container.appendChild(div);

    // ajustar file input
    const fileInput = div.querySelector(".topico-file");
    if (fileInput) {
        fileInput.name = `topicos[${index}][imagem]`;
        fileInput.dataset.fileName = `topicos_${index}_imagem`;
    }

    // inicializar TinyMCE para o novo textarea
    const ta = div.querySelector("textarea.editor");
    if (ta) initTinyMCEOnElement(ta);

    // habilitar drag handler no elemento novo
    attachDragHandlersToTopico(div, container, formId);

    renumerarTopicos(container, formId);
}

function attachDragHandlersToTopico(el, container, formId) {
    el.addEventListener("dragstart", (e) => {
        triggerTinySaveAll();
        el.classList.add("dragging");
        try { e.dataTransfer.setData("text/plain", "drag"); } catch (err) {}
    });
    el.addEventListener("dragend", () => {
        el.classList.remove("dragging");
        renumerarTopicos(container, formId);
    });
}

/* ====================== REMOVER TÓPICO ====================== */
function removeTopic(btn, formId) {
    if (!confirm("Remover este tópico?")) return;
    const topico = btn.closest(".topico");
    if (!topico) return;
    // remove instancia do tinyMCE se houver
    const ta = topico.querySelector("textarea");
    if (ta && ta.id && tinymce.get(ta.id)) tinymce.get(ta.id).remove();
    topico.remove();
    const container = document.querySelector(`#topicos-container-${formId}`);
    renumerarTopicos(container, formId);
}

function removeAllTopics(formId) {
    if (!confirm("Remover todos os tópicos?")) return;
    const container = document.querySelector(`#topicos-container-${formId}`);
    container.querySelectorAll(".topico").forEach(t => {
        const ta = t.querySelector("textarea");
        if (ta && ta.id && tinymce.get(ta.id)) tinymce.get(ta.id).remove();
        t.remove();
    });
    renumerarTopicos(container, formId);
}

/* ====================== SALVAR PARA API (reusa salvar_noticia.php) ====================== */
async function salvarNoticia(form) {
    triggerTinySaveAll();

    const fd = new FormData(form);

    // anexa arquivos dos tópicos com nome esperado
    form.querySelectorAll(".topico-file").forEach(input => {
        const dataName = input.dataset.fileName;
        if (input.files.length > 0 && dataName) {
            fd.append(dataName, input.files[0]);
        }
    });

    try {
        const res = await fetch("../apis/salvar_noticia.php", {
            method: "POST",
            body: fd
        });
        const json = await res.json();
        if (json.status === "success") {
            alert('Notícia salva com sucesso!');
            // opcional: redirecionar para lista
            // window.location.href = 'todas_as_noticias.php';
        } else {
            alert('Erro: ' + (json.message || 'erro desconhecido'));
        }
    } catch (err) {
        alert('Erro ao enviar: ' + err.message);
    }
}

/* ====================== Inicialização ====================== */
document.addEventListener("DOMContentLoaded", () => {
    // incia TinyMCE nos textareas já presentes (conteúdo principal + tópicos existentes)
    initTinyMCE();

    // habilita drag & drop no container existente
    const container = document.querySelector(`#topicos-container-<?= $noticia['id'] ?>`);
    if (container) enableDragForContainer(container, <?= $noticia['id'] ?>);

    // vincula evento salvar
    document.addEventListener("click", (e) => {
        const btn = e.target.closest(".save-btn");
        if (!btn) return;
        const form = document.getElementById("editNewsForm");
        if (!form) return;
        salvarNoticia(form);
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
