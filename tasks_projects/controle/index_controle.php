<?php
require_once "../config/connection.php";
include '../templates/gen_menu.php';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Controle - EMPARN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background-color: #f4f6f8;
            margin: 0;
        }

        .main-content {
            margin-left: 250px; /* espaço para o menu lateral */
            padding: 30px;
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

        .card-box h5 {
            margin-bottom: 15px;
            color: #333;
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
        }
    </style>
</head>
<body>

<div class="main-content">
    <h2>🎛️ Painel de Controle</h2>

    <div class="card-box">
        <h5>Gerenciar Quadradinhos do Dashboard</h5>
        <div class="btn-acoes">
            <a href="gerenciar_dashboard.php" class="btn btn-primary">Abrir Gerenciador</a>
            <a href="../apis/salvar_cards.php" class="btn btn-success">API de Criação</a>
            <a href="../apis/deletar_card.php?id=1" class="btn btn-danger">API de Exclusão (Exemplo)</a>
        </div>
    </div>

    <div class="card-box">
        <h5>Gerenciar Notícias</h5>
        <div class="btn-acoes">
            <a href="gerenciar_noticias.php" class="btn btn-primary">Abrir Gerenciador</a>
            <a href="../apis/salvar_noticia.php" class="btn btn-success">API de Criação</a>
            <a href="../apis/deletar_noticia.php?id=1" class="btn btn-danger">API de Exclusão (Exemplo)</a>
        </div>
    </div>

    <div class="card-box">
        <h5>Estatísticas Rápidas</h5>
        <div class="row text-center">
            <div class="col-md-3">
                <div class="p-3 bg-success text-white rounded shadow-sm">
                    <h6>Quadradinhos</h6>
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
                        $totalCards = $pdo->query("SELECT COUNT(*) FROM usuario")->fetchColumn();
                        echo $totalCards;
                        ?>
                    </p> <!-- Exemplo fixo -->
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

    <div class="card-box">
        <h5>Últimas Ações</h5>
        <ul class="list-group">
            <li class="list-group-item">✔️ Card "Meteorologia" adicionado</li>
            <li class="list-group-item">🗑️ Notícia "Chuvas no Sertão" excluída</li>
            <li class="list-group-item">✏️ Card "Agropecuária" atualizado</li>
            <li class="list-group-item">👤 Novo usuário cadastrado</li>
        </ul>
    </div>
</div>

<footer>
    <small>© <?= date('Y') ?> EMPARN - Painel de Controle</small>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
