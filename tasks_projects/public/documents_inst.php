<?php
require_once "../config/connection.php";

// BUSCA TÓPICOS + ARQUIVOS AGRUPADOS
$sql = "
    SELECT 
        t.id AS topico_id,
        t.nome,
        t.descricao,
        t.data_criacao,
        t.data_modificacao,
        a.id AS arq_id,
        a.nome_original,
        a.caminho_armazenado,
        a.tipo,
        a.tamanho,
        a.data_upload
    FROM documento_topico t
    LEFT JOIN documento_arquivo a ON a.topico_id = t.id
    ORDER BY t.id DESC, a.id ASC
";

$stmt = $pdo->query($sql);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// AGRUPAR RESULTADOS
$topicos = [];
foreach ($rows as $r) {
    $id = $r["topico_id"];
    if (!isset($topicos[$id])) {
        $topicos[$id] = [
            "id" => $id,
            "nome" => $r["nome"],
            "descricao" => $r["descricao"],
            "data_criacao" => $r["data_criacao"],
            "data_modificacao" => $r["data_modificacao"],
            "arquivos" => []
        ];
    }

    if ($r["arq_id"]) {
        $topicos[$id]["arquivos"][] = [
            "id" => $r["arq_id"],
            "nome_original" => $r["nome_original"],
            "caminho" => $r["caminho_armazenado"],
            "tipo" => $r["tipo"],
            "tamanho" => $r["tamanho"],
            "data_upload" => $r["data_upload"]
        ];
    }
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Informações dos Tópicos</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background: #f5f6fa;
        }
        /* Espaço para o header reutilizável */
        #header-container {
            margin-bottom: 20px;
        }
        .page-container {
            padding: 20px 0;
        }
        .btn-voltar {
            margin-bottom: 20px;
        }
        .topic-card {
            border-radius: 14px;
            padding: 25px;
            background: #fff;
            box-shadow: 0px 4px 18px rgba(0,0,0,0.08);
            margin-bottom: 30px;
            border-left: 6px solid #0d6efd;
            transition: transform .15s;
        }
        .topic-card:hover {
            transform: translateY(-3px);
        }
        .file-item {
            border-radius: 10px;
            padding: 14px;
            background: #f8f9fa;
            border: 1px solid #e2e5e9;
            margin-bottom: 12px;
        }
        .file-item:hover {
            background: #eef2f7;
        }
        .file-icon {
            font-size: 26px;
            color: #0d6efd;
            margin-right: 10px;
        }
    </style>
</head>

<body>

<!-- Header reutilizável -->
<div id="header-container">
    <?php include __DIR__ . '/../templates/header.php'; ?>
</div>

<div class="container mt-3">
    <a href="javascript:history.back();" class="btn btn-secondary mb-3">
        <i class="bi bi-arrow-left"></i> Voltar
    </a>
</div>
<div class="container page-container">

    

    <h2 class="mb-4">
        <i class="bi bi-info-circle text-primary"></i> Documentos Institucionais
    </h2>

    <?php if (empty($topicos)): ?>
        <div class="alert alert-info">Nenhum tópico encontrado.</div>
    <?php endif; ?>

    <?php foreach ($topicos as $topico): ?>
        <div class="topic-card">
            <h4 class="mb-1 text-primary">
                <i class="bi bi-folder2-open"></i>
                <?= htmlspecialchars($topico["nome"]) ?>
            </h4>

            <p class="text-muted mb-2">
                <?= nl2br(htmlspecialchars($topico["descricao"])) ?>
            </p>

            <div class="mb-3 text-muted small">
                Criado em: <?= date("d/m/Y H:i", strtotime($topico["data_criacao"])) ?><br>
                Modificado em: <?= date("d/m/Y H:i", strtotime($topico["data_modificacao"])) ?>
            </div>

            <h6 class="text-secondary mb-2">
                <i class="bi bi-paperclip"></i> Arquivos vinculados
            </h6>

            <?php if (empty($topico["arquivos"])): ?>
                <p class="text-muted"><i>Nenhum arquivo anexado.</i></p>
            <?php else: ?>
                <?php foreach ($topico["arquivos"] as $file): ?>
                    <div class="file-item d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-file-earmark file-icon"></i>
                            <div>
                                <div><strong><?= htmlspecialchars($file["nome_original"]) ?></strong></div>
                                <div class="small text-muted">
                                    <?= htmlspecialchars($file["tipo"]) ?> • <?= round($file["tamanho"]/1024) ?> KB  
                                    <br>
                                    Enviado em: <?= date("d/m/Y H:i", strtotime($file["data_upload"])) ?>
                                </div>
                            </div>
                        </div>

                        <a href="/tasks_projects/<?= htmlspecialchars($file["caminho"]) ?>" 
                           class="btn btn-outline-primary btn-sm" 
                           target="_blank">
                            <i class="bi bi-download"></i>
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

        </div>
    <?php endforeach; ?>

</div>

<!-- Rodapé -->
<footer class="text-center text-muted py-3 mt-4" style="font-size: 0.9rem;">
    <hr>
    <i class="bi bi-at me-1"></i> EMPARN 2025 — Todos os direitos reservados.
</footer>

</body>
</html>
