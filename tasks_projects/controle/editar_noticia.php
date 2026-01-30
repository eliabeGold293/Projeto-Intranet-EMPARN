<?php
require_once __DIR__ . '/../config/connection.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) die('ID inválido');

$stmt = $pdo->prepare("SELECT * FROM noticias WHERE id = ?");
$stmt->execute([$id]);
$noticia = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$noticia) die('Notícia não encontrada');

$stmt = $pdo->prepare("SELECT * FROM noticia_topicos WHERE noticia_id = ? ORDER BY ordem ASC");
$stmt->execute([$id]);
$topicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Editar Notícia</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<script src="tinymce_8.2.2/tinymce/js/tinymce/tinymce.min.js"></script>
<script src="tinymce_8.2.2/tinymce/js/tinymce/langs/pt_BR.js"></script>

<style>
body { background:#f5f7f9; }
.container-main { margin-left:250px; padding:28px; max-width:1200px; }
@media (max-width:768px){ .container-main{ margin-left:0; padding:16px; } }

.topico { cursor:grab; }

.img-preview {
    width:100%;
    max-height:360px;
    object-fit:cover;
    border-radius:.75rem;
}

/* largura padrão para tudo */
.main-fields {
    max-width: 760px;
}
/* centraliza e limita o conteúdo do card */
.card-body > form {
    width: 90%;
    margin: 0 auto;
}

/* garante alinhamento visual consistente */
.main-fields {
    max-width: 100%;
}

/* tópicos seguem o mesmo fluxo */
#topicos {
    width: 100%;
}

</style>
</head>
<body>

<?php include __DIR__ . '/../templates/gen_menu.php'; ?>

<div class="container-main">

<!-- TOPO -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Editar Notícia</h3>

    <div class="d-flex gap-2">
        <button type="button" class="btn btn-success" onclick="salvar()">
            <i class="bi bi-save"></i> Salvar
        </button>

        <a href="deletar-noticia?id=<?= $noticia['id'] ?>"
           class="btn btn-danger"
           onclick="return confirm('Tem certeza que deseja excluir esta notícia?')">
            <i class="bi bi-trash"></i> Excluir
        </a>
    </div>
</div>

<div class="card shadow-sm">
<div class="card-body">

<div class="card-content">
<form id="formNoticia" enctype="multipart/form-data">
<input type="hidden" name="id" value="<?= $noticia['id'] ?>">

<div class="mb-3">
<label class="form-label">Título</label>
<input type="text" name="titulo" class="form-control" required
       value="<?= htmlspecialchars($noticia['titulo']) ?>">
</div>

<div class="mb-3">
<label class="form-label">Subtítulo</label>
<input type="text" name="subtitulo" class="form-control"
       value="<?= htmlspecialchars($noticia['subtitulo']) ?>">
</div>

<div class="mb-3">
<label class="form-label">Autoria</label>
<input type="text" name="autoria" class="form-control"
       value="<?= htmlspecialchars($noticia['autoria']) ?>">
</div>

<?php if ($noticia['imagem']): ?>
<div class="mb-3">
<img src="/tasks_projects/uploads/<?= htmlspecialchars($noticia['imagem']) ?>" class="img-preview">
</div>
<?php endif; ?>

<div class="mb-3">
<label class="form-label">Imagem principal</label>
<input type="file" name="imagem" class="form-control">
</div>

<div class="mb-3">
<label class="form-label">Fonte da imagem</label>
<textarea name="fonte_imagem" class="form-control"><?= htmlspecialchars($noticia['fonte_imagem']) ?></textarea>
</div>

<div class="mb-3">
<label class="form-label">Conteúdo</label>
<textarea name="texto" class="form-control editor"><?= htmlspecialchars($noticia['texto']) ?></textarea>
</div>

<div class="mb-4">
<label class="form-label">Link externo (opcional)</label>
<input type="text" name="link" class="form-control"
       value="<?= htmlspecialchars($noticia['link']) ?>">
</div>

<hr>

<h5>Tópicos</h5>

<div id="topicos">
<?php foreach ($topicos as $i => $t): ?>
<div class="topico border rounded p-3 mb-3">

<input type="hidden" name="topicos[<?= $i ?>][id]" value="<?= $t['id'] ?>">

<div class="d-flex justify-content-between mb-3">
<strong>Tópico <?= $i+1 ?></strong>
<button type="button" class="btn btn-sm btn-outline-danger"
        onclick="confirmarRemocao(this)">Remover</button>
</div>

<div class="mb-2">
<label class="form-label">Título do tópico</label>
<input type="text" name="topicos[<?= $i ?>][titulo]" class="form-control"
       value="<?= htmlspecialchars($t['titulo']) ?>">
</div>

<?php if ($t['imagem']): ?>
<img src="/tasks_projects/uploads/<?= htmlspecialchars($t['imagem']) ?>" class="img-preview my-2">
<?php endif; ?>

<div class="mb-2">
<label class="form-label">Imagem do tópico</label>
<input type="file" name="topicos[<?= $i ?>][imagem]" class="form-control">
</div>

<div class="mb-2">
<label class="form-label">Conteúdo do tópico</label>
<textarea name="topicos[<?= $i ?>][texto]" class="form-control editor"><?= htmlspecialchars($t['texto']) ?></textarea>
</div>

</div>
<?php endforeach; ?>
</div>

<button type="button" class="btn btn-outline-secondary mt-2" onclick="addTopico()">
<i class="bi bi-plus-circle"></i> Adicionar tópico
</button>

</form>
</div>

</div>
</div>

</div>

<script>
const TINYMCE_CONFIG = {
    license_key: 'gpl',
    language: 'pt_BR',
    height: 300,
    menubar: true,
    plugins: 'lists link image table code fullscreen',
    toolbar: 'undo redo | bold italic | bullist numlist | link image | fullscreen code',
    branding: false,
    promotion: false
};

function initTinyMCE() {
    document.querySelectorAll('textarea.editor').forEach(el => {
        if (!el.id) el.id = 'mce_' + Math.random().toString(36).substr(2,9);
        if (!tinymce.get(el.id)) {
            tinymce.init({ ...TINYMCE_CONFIG, target: el });
        }
    });
}

function confirmarRemocao(botao) {
    if (confirm('Deseja realmente remover este tópico?')) {
        botao.closest('.topico').remove();
    }
}

function addTopico() {
    const c = document.getElementById('topicos');
    const i = c.children.length;

    const d = document.createElement('div');
    d.className = 'topico border rounded p-3 mb-3';
    d.innerHTML = `
        <input type="hidden" name="topicos[${i}][id]">

        <div class="d-flex justify-content-between mb-3">
            <strong>Tópico ${i+1}</strong>
            <button type="button" class="btn btn-sm btn-outline-danger"
                onclick="confirmarRemocao(this)">Remover</button>
        </div>

        <div class="mb-2">
            <label class="form-label">Título do tópico</label>
            <input type="text" name="topicos[${i}][titulo]" class="form-control">
        </div>

        <div class="mb-2">
            <label class="form-label">Imagem do tópico</label>
            <input type="file" name="topicos[${i}][imagem]" class="form-control">
        </div>

        <div class="mb-2">
            <label class="form-label">Conteúdo do tópico</label>
            <textarea name="topicos[${i}][texto]" class="form-control editor"></textarea>
        </div>
    `;
    c.appendChild(d);
    initTinyMCE();
}

function salvar() {
    if (!confirm('Deseja salvar as alterações desta notícia?')) return;
    tinymce.triggerSave();
    const fd = new FormData(document.getElementById('formNoticia'));

    fetch('salvar-noticia', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(j => {
            if (j.status === 'success') {
                alert('Notícia salva com sucesso!');
                location.href = 'view-noticias-existentes';
            } else alert(j.message || 'Erro');
        });
}

document.addEventListener('DOMContentLoaded', initTinyMCE);
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
