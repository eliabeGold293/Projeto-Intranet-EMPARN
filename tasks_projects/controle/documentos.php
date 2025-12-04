<?php require_once "../config/connection.php"; ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Administração de Documentos</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">

    <style>
        body {
            background-color: #eef1f4;
            margin: 0;
            display: flex;
        }

        .main-content {
            flex: 1;
            padding: 35px;
            margin-left: 250px;
        }

        @media(max-width: 768px){
            .main-content {
                margin-left: 0;
            }
        }

        .page-title {
            font-size: 2rem;
            font-weight: 700;
            color: #0046a0;
            margin-bottom: 25px;
        }

        .card-modern {
            background: #fff;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 4px 14px rgba(0,0,0,0.08);
            border: none;
        }

        .topic-box {
            border: 2px dashed #0d6efd;
            border-radius: 10px;
            padding: 30px;
            text-align: center;
            cursor: pointer;
            transition: 0.3s;
        }

        .topic-box:hover {
            background: #e8f1ff;
        }

        .topic-form {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            border: 1px solid #d8d8d8;
            margin-bottom: 20px;
        }

        .remove-topic {
            float: right;
            cursor: pointer;
        }

        .file-row {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
        }

        /* Caixa da tabela com barra de rolagem */
        .table-scroll {
            max-height: 400px;
            overflow-y: auto;
            border: 1px solid #d8d8d8;
            border-radius: 8px;
        }

        /* Cabeçalho fixo */
        .table-scroll thead th {
            position: sticky;
            top: 0;
            background: #cfe2ff !important;
            z-index: 5;
        }
    </style>
</head>

<body>
<?php include '../templates/gen_menu.php'; ?>

<main class="main-content">

    <h2 class="page-title">
        <i class="bi bi-folder-plus"></i> Administração de Documentos Institucionais
    </h2>

    <div class="card-modern">

        <div id="topicsContainer"></div>

        <!-- Botão criar tópico -->
        <div class="topic-box mt-4" id="addTopicBtn">
            <i class="bi bi-plus-circle" style="font-size: 2.5rem; color: #0d6efd;"></i>
            <p class="mt-2 mb-0">Adicionar Novo Tópico de Documentos</p>
        </div>

        <!-- Botão final -->
        <button id="saveAllBtn" class="btn btn-success mt-4">
            <i class="bi bi-check2-circle"></i> Salvar Tudo
        </button>

        <div id="message" class="mt-3"></div>

    </div>

    <?php
    // Buscar tópicos com arquivos
    $query = "
        SELECT 
            t.id AS topico_id,
            t.nome AS topico_nome,
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

    // Agrupar por tópico
    $topicos = [];
    foreach ($dados as $row) {
        $id = $row['topico_id'];

        if (!isset($topicos[$id])) {
            $topicos[$id] = [
                'id' => $id,
                'nome' => $row['topico_nome'],
                'data_criacao' => $row['data_criacao'],
                'data_modificacao' => $row['data_modificacao'],
                'arquivos' => []
            ];
        }


        if ($row['arquivo_id']) {
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
    ?>

    <hr class="my-4">

    <h4 class="mb-3"><i class="bi bi-table"></i> Tópicos Cadastrados</h4>

    <!-- CAMPO DE BUSCA -->
    <input 
        type="text" 
        id="searchInput" 
        class="form-control mb-3"
        placeholder="Pesquisar por nome do tópico ou data (ex: 2024-11)...">
    
    <!-- TABELA COM ROLAGEM -->
    <div class="table-scroll">
        <table class="table table-striped table-hover" id="topicsTable">
            <thead class="table-primary">
                <tr>
                    <th>Tópico</th>
                    <th>Arquivos</th>
                    <th>Criado / Modificado</th>
                    <th style="width: 120px;">Ações</th>
                </tr>
            </thead>        


            <tbody>
                <?php if (count($topicos) === 0): ?>
                    <tr>
                        <td colspan="3" class="text-center text-muted">Nenhum tópico cadastrado.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($topicos as $topico): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($topico['nome']) ?></strong></td>

                            <td>
                                <?php if (count($topico['arquivos']) === 0): ?>
                                    <span class="text-muted">Nenhum arquivo</span>
                                <?php else: ?>
                                    <ul class="mb-0">
                                        <?php foreach ($topico['arquivos'] as $arq): ?>
                                            <li>
                                                <a href="/tasks_projects/<?= htmlspecialchars($arq['caminho']) ?>" target="_blank">
                                                    <?= htmlspecialchars($arq['nome_original']) ?>
                                                </a>
                                                <br>
                                                <small class="text-muted">
                                                    (<?= $arq['tipo'] ?> • <?= round($arq['tamanho'] / 1024, 1) ?> KB)
                                                </small>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </td>

                            <td>
                                <small>
                                    Criado: <strong><?= date("d/m/Y H:i", strtotime($topico['data_criacao'])) ?></strong><br>
                                    Modificado: <strong><?= date("d/m/Y H:i", strtotime($topico['data_modificacao'])) ?></strong>
                                </small>
                            </td>

                            <td class="text-center">
                                <!-- Botão Editar -->
                                <a href="editar_topico_doc.php?id=<?= $topico['id'] ?>" class="btn btn-sm btn-primary"><i class="bi bi-pencil-square text-white"></i></a>


                                <button class="btn btn-sm btn-danger"
                                        onclick="deleteTopicFromDatabase(<?= $topico['id'] ?>)">
                                    <i class="bi bi-trash text-white"></i>
                                </button>

                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../public/js/admin_doc.js"></script>

<script>
// FILTRO DE PESQUISA (nome do tópico e datas)
document.getElementById("searchInput").addEventListener("keyup", function() {
    let filtro = this.value.toLowerCase();
    let linhas = document.querySelectorAll("#topicsTable tbody tr");

    linhas.forEach(linha => {
        let textoLinha = linha.innerText.toLowerCase();
        linha.style.display = textoLinha.includes(filtro) ? "" : "none";
    });
});
</script>

</body>
</html>
