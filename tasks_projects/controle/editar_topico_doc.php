<?php
require_once "../config/connection.php";

// --- Buscar tópicos + arquivos (mesma lógica que você já usa) ---
$query = "
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
    ORDER BY t.id DESC, a.id ASC
";

$stmt = $pdo->query($query);
$dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Agrupar por tópico (inclui id, nome, descricao, arquivos)
$topicos = [];
foreach ($dados as $row) {
    $id = (int)$row['topico_id'];

    if (!isset($topicos[$id])) {
        $topicos[$id] = [
            'id' => $id,
            'nome' => $row['topico_nome'],
            'descricao' => $row['topico_descricao'] ?? '',
            'data_criacao' => $row['data_criacao'],
            'data_modificacao' => $row['data_modificacao'],
            'arquivos' => []
        ];
    }

    if (!empty($row['arquivo_id'])) {
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

// Converter para array indexado (JSON será mais limpo)
$topicos_indexed = array_values($topicos);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Editar Tópicos — Administração de Documentos</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body { background: #f8f9fa; }
        .card { border-radius: 12px; }
        .topic-box { border: 2px dashed #0d6efd; border-radius: 10px; padding: 20px; text-align: center; cursor: pointer; }
        .topic-form { background: #fff; border-radius: 10px; padding: 18px; border: 1px solid #e7e7e7; margin-bottom: 14px; }
        .file-row { display:flex; gap:10px; margin-bottom:8px; align-items:center; }
        .existing-file { display:flex; justify-content:space-between; align-items:center; padding:8px; border:1px solid #eee; border-radius:6px; margin-bottom:6px; }
        .remove-topic { float:right; cursor:pointer; }
    </style>
</head>
<body class="p-4">

<?php include '../templates/gen_menu.php'; ?>

<main class="container">
    <div class="row justify-content-center">
        <div class="col-xl-10">

            <h2 class="mb-3"><i class="bi bi-pencil-square"></i> Editar Tópicos</h2>

            <div class="card p-3 mb-3">
                <div class="d-flex gap-2">
                    <div id="addTopicBtn" class="topic-box me-auto" role="button" title="Adicionar tópico">
                        <i class="bi bi-plus-circle" style="font-size: 1.6rem; color: #0d6efd;"></i>
                        <div class="small mt-1">Adicionar Novo Tópico</div>
                    </div>

                    <button id="saveAllBtn" class="btn btn-success ms-2">
                        <i class="bi bi-check2-circle"></i> Salvar Tudo
                    </button>

                    <a href="documentos.php" class="btn btn-secondary ms-2">
                        <i class="bi bi-arrow-left"></i> Voltar
                    </a>
                </div>
                <div id="message" class="mt-3"></div>
            </div>

            <div id="topicsContainer"></div>

            <hr>

            <h5 class="text-muted">Tópicos carregados do servidor (você pode editá-los, adicionar novos ou remover).</h5>
        </div>
    </div>
</main>

<!-- Injeta dados existentes do PHP para o JS -->
<script>
    // existingTopics será consumido pelo JS dinâmico
    const existingTopics = <?= json_encode($topicos_indexed, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
</script>

<!-- Versão consolidada do JS dinâmico (adaptada ao layout acima) -->
<script src="../public/js/edit_doc.js"></script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
