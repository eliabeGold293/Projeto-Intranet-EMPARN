<?php
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

$usuarioLogado = $_SESSION['usuario_id'] ?? null;

$grauAcesso = 0;

if ($usuarioLogado) {

    $stmtPermissao = $pdo->prepare("
        SELECT cu.grau_acesso
        FROM usuario u
        INNER JOIN classe_usuario cu ON cu.id = u.classe_id
        WHERE u.id = ?
    ");

    $stmtPermissao->execute([$usuarioLogado]);
    $grauAcesso = (int) $stmtPermissao->fetchColumn();
}

$stmt = $pdo->prepare("
    SELECT au.*, u.nome 
    FROM arquivo_usuario au
    LEFT JOIN usuario u ON u.id = au.usuario_id
    ORDER BY au.data_upload DESC
");
$stmt->execute();
$arquivos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Uploads de Usuários</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>
body { margin: 0; }

.caixa-flutuante {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    display: flex;
    flex-direction: column;
    gap: 15px;
    width: 400px;
    max-width: 90%;
    padding: 25px;
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
    z-index: 9999;
}

.btn-fechar {
    margin-left: auto;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: transparent;
    border: none;
    font-size: 20px;
    color: #555;
    cursor: pointer;
    border-radius: 50%;
}

.btn-fechar:hover {
    background: #f1f1f1;
    color: #000;
}
</style>
</head>

<body class="bg-light">

<?php include __DIR__ . '/../templates/header.php'; ?>

<div class="container py-4">

    <div class="mb-3">
        <a href="javascript:history.back();" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Uploads do Servidor</h2>
        <?php if ($grauAcesso >= 2): ?>
            <button onclick="AbrirFormEnvio()" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Adicionar Arquivo
            </button>
        <?php endif; ?>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">

            <h4 class="mb-3">Arquivos disponíveis</h4>

            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Arquivo</th>
                            <th>Descrição</th>
                            <th>Enviado por</th>
                            <th>Data</th>
                            <th width="150">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (count($arquivos) > 0): ?>
                        <?php foreach ($arquivos as $arquivo): ?>
                            <tr>
                                <td><?= htmlspecialchars($arquivo['nome_original']) ?></td>
                                <td class="text-center">
                                    <?php if (!empty($arquivo['descricao'])): ?>

                                        <button class="btn btn-sm btn-outline-info"
                                            onclick="abrirDescricao(
                                                `<?= htmlspecialchars($arquivo['nome_original'], ENT_QUOTES) ?>`,
                                                `<?= htmlspecialchars($arquivo['descricao'], ENT_QUOTES) ?>`
                                            )">
                                            <i class="bi bi-info-circle"></i>
                                        </button>

                                    <?php else: ?>

                                        <span class="text-muted">—</span>

                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($arquivo['nome'] ?? '—') ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($arquivo['data_upload'])) ?></td>
                                <td>
                                    <div class="d-flex gap-2">

                                        <!-- Download -->
                                        <a href="<?= htmlspecialchars($arquivo['caminho_armazenado']) ?>"
                                           class="btn btn-sm btn-success"
                                           target="_blank">
                                           <i class="bi bi-download"></i>
                                        </a>
                            
                                        <?php if ($usuarioLogado && $usuarioLogado == $arquivo['usuario_id'] && $grauAcesso >= 2): ?>
    
                                            <!-- EDITAR -->
                                            <button class="btn btn-sm btn-warning"
                                                onclick="abrirEdicao(
                                                    <?= $arquivo['id'] ?>,
                                                    `<?= htmlspecialchars($arquivo['descricao'] ?? '', ENT_QUOTES) ?>`
                                                )">
                                                <i class="bi bi-pencil"></i>
                                            </button>

                                            <!-- EXCLUIR -->
                                            <button class="btn btn-sm btn-danger"
                                                onclick="confirmarExclusao(<?= $arquivo['id'] ?>)">
                                                <i class="bi bi-trash"></i>
                                            </button>

                                        <?php endif; ?>

                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted">
                                Nenhum arquivo disponível.
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

</div>

<script>

    function AbrirFormEnvio(){

        const caixaEnvioDoc = document.createElement('div');
        caixaEnvioDoc.id = "caixaEnvio";
        caixaEnvioDoc.className = "caixa-flutuante";

        caixaEnvioDoc.innerHTML = `
            <button class="btn-fechar" onclick="fecharCaixa()">&times;</button>
            <h5 class="mb-3">Enviar Novo Documento</h5>

            <form method="POST" enctype="multipart/form-data" action="enviar-arquivo-us" class="d-flex flex-column gap-3">

                <div>
                    <label class="form-label">Selecione o arquivo</label>
                    <input type="file" class="form-control" name="arquivo" id="arquivo" required>
                </div>

                <div>
                    <label class="form-label">Descrição (opcional)</label>
                    <textarea 
                        name="descricao" 
                        class="form-control" 
                        rows="3"
                        placeholder="Digite uma descrição para este arquivo..."
                    ></textarea>
                </div>

                <button type="submit" class="btn btn-primary" id="btn-enviar" style="display:none;">
                    <i class="bi bi-upload"></i> Enviar
                </button>

            </form>
        `;

        document.body.appendChild(caixaEnvioDoc);

        const inputArquivo = document.getElementById("arquivo");
        const botaoEnviar = document.getElementById("btn-enviar");

        inputArquivo.addEventListener("change", function() {
            botaoEnviar.style.display =
                inputArquivo.files.length > 0 ? "block" : "none";
        });
    }

    function fecharCaixa(){
        const caixa = document.getElementById("caixaEnvio");
        if(caixa) caixa.remove();
    }

    function abrirEdicao(id, descricaoAtual){

        const caixa = document.createElement('div');
        caixa.id = "caixaEnvio";
        caixa.className = "caixa-flutuante";

        caixa.innerHTML = `
            <button class="btn-fechar" onclick="fecharCaixa()">&times;</button>
            <h5 class="mb-3">Editar Documento</h5>

            <form method="POST" enctype="multipart/form-data" action="editar-arquivo-us" class="d-flex flex-column gap-3">

                <input type="hidden" name="id" value="${id}">

                <div>
                    <label class="form-label">Descrição</label>
                    <textarea name="descricao" class="form-control" rows="3">${descricaoAtual}</textarea>
                </div>

                <div>
                    <label class="form-label">Substituir arquivo (opcional)</label>
                    <input type="file" class="form-control" name="arquivo">
                </div>

                <button type="submit" class="btn btn-warning">
                    <i class="bi bi-check-circle"></i> Atualizar
                </button>

            </form>
        `;

        document.body.appendChild(caixa);
    }

    function confirmarExclusao(id){

        if(confirm("Tem certeza que deseja excluir este arquivo? Essa ação não pode ser desfeita.")){

            const form = document.createElement("form");
            form.method = "POST";
            form.action = "excluir-arquivo-us";

            const input = document.createElement("input");
            input.type = "hidden";
            input.name = "id";
            input.value = id;

            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        }
    }

    function abrirDescricao(nomeArquivo, descricao){

        const caixa = document.createElement('div');
        caixa.id = "caixaEnvio";
        caixa.className = "caixa-flutuante";

        caixa.innerHTML = `
            <button class="btn-fechar" onclick="fecharCaixa()">&times;</button>

            <div class="mb-3">
                <h5 class="mb-1">
                    <i class="bi bi-file-earmark-text"></i> ${nomeArquivo}
                </h5>
                <small class="text-muted">Descrição do arquivo</small>
            </div>

            <div class="border rounded p-3 bg-light" style="max-height:200px; overflow:auto;">
                ${descricao.replace(/\n/g, "<br>")}
            </div>

            <div class="text-end mt-3">
                <button class="btn btn-secondary btn-sm" onclick="fecharCaixa()">
                    Fechar
                </button>
            </div>
        `;

        document.body.appendChild(caixa);
    }
</script>

</body>
</html>