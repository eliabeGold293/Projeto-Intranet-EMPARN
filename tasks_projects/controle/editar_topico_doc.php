<?php
// editar_topico.php
require_once __DIR__ . '/../config/connection.php';
session_start();

// --- Autenticação (ajuste conforme sua lógica) ---
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.php");
    exit;
}

// --- CSRF token simples ---
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(24));
}
$csrf = $_SESSION['csrf_token'];

// --- Validar ID recebido pela URL ---
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    echo "ID inválido.";
    exit;
}

$topicoId = (int) $_GET['id'];

// --- Buscar 1 tópico + seus arquivos (prepared statement) ---
$sql = "
    SELECT 
        t.id AS topico_id,
        t.nome AS topico_nome,
        COALESCE(t.descricao, '') AS topico_descricao,
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
    WHERE t.id = :id
    ORDER BY a.id ASC
";

$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $topicoId]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$rows) {
    // tópico inexistente
    http_response_code(404);
    echo "Tópico não encontrado.";
    exit;
}

// --- Monta estrutura do tópico (1 tópico com array de arquivos) ---
$topico = [
    'id' => (int)$rows[0]['topico_id'],
    'nome' => $rows[0]['topico_nome'],
    'descricao' => $rows[0]['topico_descricao'],
    'data_criacao' => $rows[0]['data_criacao'],
    'data_modificacao' => $rows[0]['data_modificacao'],
    'arquivos' => []
];

foreach ($rows as $r) {
    if (!empty($r['arquivo_id'])) {
        $topico['arquivos'][] = [
            'id' => (int)$r['arquivo_id'],
            'nome_original' => $r['nome_original'],
            'caminho' => $r['caminho_armazenado'],
            'tipo' => $r['tipo'],
            'tamanho' => $r['tamanho'],
            'data_upload' => $r['data_upload']
        ];
    }
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Editar Tópico — <?= htmlspecialchars($topico['nome']) ?></title>
    <meta name="viewport" content="width=device-width,initial-scale=1">

    <!-- Bootstrap CSS + Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body { background: #f8f9fa; }
        .card-modern { background:#fff; border-radius:10px; padding:20px; box-shadow:0 6px 20px rgba(0,0,0,0.06); }
        .file-item { background:#f8f9fa; border:1px solid #e9ecef; padding:12px; border-radius:8px; margin-bottom:10px; }
        .file-actions .btn { margin-left:6px; }
        .small-muted { color:#6c757d; font-size:.9em; }

        .card-custom {
            border-radius: 20px;
            border: none;
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
        }
        .file-box {
            border: 1px dashed #bbb;
            border-radius: 12px;
            padding: 18px;
            background: #fff;
        }
        .file-existing {
            border-bottom: 1px solid #eee;
            padding: 12px 4px;
        }
        .file-existing:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body class="p-4">

<?php include __DIR__ . '/../templates/gen_menu.php'; ?>

<main class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10">

            <div class="d-flex align-items-center mb-3">
                <h2 class="mb-0"><i class="bi bi-pencil-square"></i> Editar Tópico</h2>
            </div>

            <div class="card-modern mb-4">

                <!-- METADADOS DO TÓPICO -->
                <form id="formEditarTopico" method="POST" action="../apis/salvar_edicao_topico.php" enctype="multipart/form-data" class="row g-3">
                    <input type="hidden" name="action" value="update_topic">
                    <input type="hidden" name="id" value="<?= $topico['id'] ?>">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">

                    <div class="col-12">
                        <label class="form-label fw-semibold">Nome do Tópico</label>
                        <input type="text" name="nome" class="form-control" value="<?= htmlspecialchars($topico['nome']) ?>" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Descrição (opcional)</label>
                        <textarea name="descricao" class="form-control" rows="3"><?= htmlspecialchars($topico['descricao']) ?></textarea>
                    </div>

                    <!-- ARQUIVOS EXISTENTES (renderizados pelo PHP) -->
                    <div class="col-12">
                        <h5 class="mt-3">Arquivos anexados</h5>

                        <?php if (empty($topico['arquivos'])): ?>
                            <div class="alert alert-warning">Nenhum arquivo anexado.</div>
                        <?php else: ?>
                            <div id="existingFilesList" class="mb-2">
                                <?php foreach ($topico['arquivos'] as $arq): ?>
                                    <div class="file-item d-flex justify-content-between align-items-center" data-file-id="<?= $arq['id'] ?>">
                                        <div>
                                            <i class="bi bi-file-earmark-fill me-2 text-primary" style="font-size:1.3rem;"></i>
                                            <strong><?= htmlspecialchars($arq['nome_original']) ?></strong>
                                            <div class="small-muted">
                                                <?= htmlspecialchars($arq['tipo']) ?> • <?= round($arq['tamanho']/1024, 1) ?> KB
                                                <br>
                                                Enviado em: <?= date("d/m/Y H:i", strtotime($arq['data_upload'])) ?>
                                            </div>
                                        </div>

                                        <div class="file-actions d-flex align-items-center">
                                            <!-- Download: link direto (ajuste o prefix se necessário) -->
                                            <a href="/tasks_projects/<?= rawurlencode($arq['caminho']) ?>" target="_blank" class="btn btn-outline-secondary btn-sm" title="Baixar">
                                                <i class="bi bi-download"></i>
                                            </a>

                                            <!-- Deletar: via AJAX -->
                                            <button type="button" class="btn btn-danger btn-sm btn-delete-file" data-file-id="<?= $arq['id'] ?>">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Adicionar Novos Arquivos</label>

                        <div id="newFilesArea"></div>

                        <button type="button" id="btnAddFile" class="btn btn-outline-secondary mt-2">
                            <i class="bi bi-plus-circle"></i> Adicionar arquivo
                        </button>

                    </div>

                    <!-- AÇÕES -->
                    <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                        <a href="gerenciar-documentos-institucionais" class="btn btn-secondary">Voltar</a>
                        <button type="submit" id="btnSaveTopic" class="btn btn-primary">
                            <i class="bi bi-save"></i> Salvar Alterações
                        </button>
                    </div>

                </form>

            </div>

        </div>
    </div>
</main>

<!-- Modal simples para feedback -->
<div class="modal fade" id="feedbackModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-body" id="feedbackModalBody"></div>
    </div>
  </div>
</div>

<!-- Bootstrap JS bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", () => {

        // ============================================
        // 1) ADICIONAR NOVOS ARQUIVOS ILIMITADOS
        // ============================================

        const btnAddFile = document.getElementById("btnAddFile");
        const newFilesArea = document.getElementById("newFilesArea");

        if (btnAddFile) {
            btnAddFile.addEventListener("click", () => {
                const box = document.createElement("div");
                box.className = "d-flex gap-2 mt-2";

                box.innerHTML = `
                    <input type="file" name="novos_arquivos[]" class="form-control">
                    <button type="button" class="btn btn-outline-danger btn-sm remove-field">
                        <i class="bi bi-x-lg"></i>
                    </button>
                `;

                box.querySelector(".remove-field").onclick = () => box.remove();

                newFilesArea.appendChild(box);
            });
        }



        // ============================================
        // 2) REMOVER ARQUIVO EXISTENTE (AJAX)
        // ============================================

        // todos os arquivos existentes estão dentro do container .existing-file-wrapper
        document.body.addEventListener("click", async (ev) => {
            const btn = ev.target.closest(".btn-delete-file");
            if (!btn) return;

            const fileId = btn.dataset.fileId;
            if (!fileId) return;

            if (!confirm("Deseja remover este arquivo definitivamente?")) return;

            // loading
            btn.disabled = true;
            btn.innerHTML = `
                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            `;

            try {
                const res = await fetch("remover-arquivo-doc", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: "id=" + encodeURIComponent(fileId)
                });

                const json = await res.json();

                if (json.status === "success") {
                    // remove o bloco visual
                    const row = document.querySelector(`[data-file-id="${fileId}"]`);
                    if (row) row.remove();

                    showMessage("Arquivo removido com sucesso.", "success");

                } else {
                    showMessage(json.message || "Erro ao remover arquivo.", "danger");
                    btn.disabled = false;
                    btn.innerHTML = `<i class="bi bi-trash"></i>`;
                }

            } catch (e) {
                console.error(e);
                showMessage("Erro inesperado ao remover arquivo.", "danger");
                btn.disabled = false;
                btn.innerHTML = `<i class="bi bi-trash"></i>`;
            }
        });



        // ============================================
        // 3) VALIDAÇÃO DO FORMULÁRIO
        // ============================================

        const form = document.getElementById("formEditarTopico");
        if (form) {
            form.addEventListener("submit", (ev) => {
                const nome = form.querySelector('input[name="nome"]').value.trim();

                if (!nome) {
                    ev.preventDefault();
                    showMessage("O nome do tópico é obrigatório.", "warning");
                    form.querySelector('input[name="nome"]').focus();
                    return false;
                }
            });
        }

        // ============================================
        // 4) MODAL DE FEEDBACK
        // ============================================

        function showMessage(text, type = "info") {
            const body = document.getElementById("feedbackModalBody");
            body.innerHTML = `<div class="alert alert-${type} mb-0">${text}</div>`;

            const modal = new bootstrap.Modal(document.getElementById("feedbackModal"));
            modal.show();

            setTimeout(() => modal.hide(), 1600);
        }

        // ============================================
        // 5) SALVAR ALTERAÇÕES VIA AJAX (FETCH POST)
        // ============================================

        if (form) {
            form.addEventListener("submit", async (ev) => {
                ev.preventDefault(); // evita reload da página

                const nome = form.querySelector('input[name="nome"]').value.trim();

                if (!nome) {
                    showMessage("O nome do tópico é obrigatório.", "warning");
                    form.querySelector('input[name="nome"]').focus();
                    return;
                }

                const submitBtn = form.querySelector("button[type=submit]");
                const originalBtnHTML = submitBtn.innerHTML;

                // loading
                submitBtn.disabled = true;
                submitBtn.innerHTML = `
                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                    Salvando...
                `;

                // prepara os dados (inclui arquivos)
                const formData = new FormData(form);

                try {
                    const response = await fetch("salvar-edicao-documento", {
                        method: "POST",
                        body: formData
                    });

                    const json = await response.json();

                    if (json.status === "success") {
                        showMessage("Alterações salvas com sucesso!", "success");

                        // atualizar a página após 1.2s
                        setTimeout(() => {
                            window.location.reload();
                        }, 1200);

                    } else {
                        showMessage(json.message || "Erro ao salvar alterações", "danger");
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnHTML;
                    }

                } catch (error) {
                    console.error(error);
                    showMessage("Erro inesperado ao salvar.", "danger");
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnHTML;
                }
            });
        }


    });

</script>

</body>
</html>
