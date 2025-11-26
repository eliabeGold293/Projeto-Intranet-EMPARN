<?php
require_once "../config/connection.php";
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Controle - EMPARN</title>
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">

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
            margin-left: 250px; /* espaço para o menu lateral */
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 20px;
            }

            footer {
                margin-left: 0;
            }
        }

        h2 {
            font-size: 1.8rem;
            font-weight: bold;
            color: #0046a0;
            margin-bottom: 20px;
        }

        .card-box {
            background: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .btn-acoes {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        footer {
            margin-left: 250px;
            background: #e9ecef;
            padding: 15px;
            text-align: center;
            border-top: 1px solid #ccc;
            margin-top: 40px;
        }

        #listaAcoes {
            max-height: 500px;
            overflow-y: auto;
        }
    </style>
</head>
<body>
    <!-- Menu reutilizável -->
    <?php include '../templates/gen_menu.php'; ?>

    <!-- Conteúdo principal -->
    <main class="main-content">
        <h2><i class="bi bi-grid"></i> Painel de Controle</h2>

        <!-- Gerenciar Cards -->
        <div class="card-box">
            <h5>Gerenciar Cards do Dashboard</h5>
            <div class="btn-acoes">
                <a href="gerenciar_dashboard.php" class="btn btn-primary">Abrir Gerenciador</a>
                <a href="../apis/salvar_cards.php" class="btn btn-success">API de Criação</a>
                <a href="../apis/deletar_card.php?id=1" class="btn btn-danger">API de Exclusão (Exemplo)</a>
            </div>
        </div>

        <!-- Gerenciar Notícias -->
        <div class="card-box">
            <h5>Gerenciar Notícias</h5>
            <div class="btn-acoes">
                <a href="gerenciar_noticias.php" class="btn btn-primary">Abrir Gerenciador</a>
                <a href="../apis/salvar_noticia.php" class="btn btn-success">API de Criação</a>
                <a href="../apis/deletar_noticia.php?id=1" class="btn btn-danger">API de Exclusão (Exemplo)</a>
            </div>
        </div>

        <!-- Estatísticas Rápidas -->
        <div class="card-box">
            <h5>Estatísticas Rápidas</h5>
            <div class="row text-center">
                <div class="col-md-3">
                    <div class="p-3 bg-success text-white rounded shadow-sm">
                        <h6>Cards</h6>
                        <p class="fs-4">
                            <?php
                            $totalCards = $pdo->query("SELECT COUNT(*) FROM dashboard")->fetchColumn();
                            echo $totalCards;
                            ?>
                        </p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3 bg-primary text-white rounded shadow-sm">
                        <h6>Notícias</h6>
                        <p class="fs-4">
                            <?php
                            $totalNoticias = $pdo->query("SELECT COUNT(*) FROM noticias")->fetchColumn();
                            echo $totalNoticias;
                            ?>
                        </p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3 bg-warning text-dark rounded shadow-sm">
                        <h6>Usuários</h6>
                        <p class="fs-4">
                            <?php
                            $totalUsuarios = $pdo->query("SELECT COUNT(*) FROM usuario")->fetchColumn();
                            echo $totalUsuarios;
                            ?>
                        </p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3 bg-dark text-white rounded shadow-sm">
                        <h6>Acessos Hoje</h6>
                        <p class="fs-4">89</p> <!-- Exemplo fixo -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Últimas Ações -->
        <div class="card-box">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h5 class="mb-0">Últimas Ações</h5>
                <div class="d-flex gap-2 ms-3">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input filtro-acao" type="checkbox" id="filtroTodos" value="TODOS" checked>
                        <label class="form-check-label" for="filtroTodos">Todos</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input filtro-acao" type="checkbox" id="filtroCriacao" value="INSERIR">
                        <label class="form-check-label" for="filtroCriacao">Criações</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input filtro-acao" type="checkbox" id="filtroAtualizacao" value="ATUALIZAR">
                        <label class="form-check-label" for="filtroAtualizacao">Atualizações</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input filtro-acao" type="checkbox" id="filtroExclusao" value="EXCLUIR">
                        <label class="form-check-label" for="filtroExclusao">Deleções</label>
                    </div>
                </div>
            </div>

            <ul class="list-group" id="listaAcoes">
                <?php
                $stmt = $pdo->query("SELECT descricao, acao, data_acao 
                                    FROM log_acao 
                                    ORDER BY data_acao DESC");
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo "<li class='list-group-item acao-item' data-acao='{$row['acao']}'>
                            {$row['descricao']} 
                            <small class='text-muted'>(" . date('d/m/Y H:i', strtotime($row['data_acao'])) . ")</small>
                        </li>";
                }
                ?>
            </ul>
        </div>
    </main>

    <footer>
        <small>© <?= date('Y') ?> EMPARN - Painel de Controle</small>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function aplicarFiltro() {
            const todos = document.getElementById('filtroTodos').checked;
            const ativos = Array.from(document.querySelectorAll('.filtro-acao:checked'))
                                .map(c => c.value);

            document.querySelectorAll('#listaAcoes .acao-item').forEach(item => {
                if (todos || ativos.includes(item.dataset.acao)) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        document.querySelectorAll('.filtro-acao').forEach(chk => {
            chk.addEventListener('change', () => {
                if (chk.value === 'TODOS' && chk.checked) {
                    document.querySelectorAll('.filtro-acao').forEach(c => {
                        if (c.value !== 'TODOS') c.checked = false;
                    });
                } else {
                    if (chk.value !== 'TODOS' && chk.checked) {
                        document.getElementById('filtroTodos').checked = false;
                    }
                }
                aplicarFiltro();
            });
        });

        aplicarFiltro();
    </script>
</body>
</html>
