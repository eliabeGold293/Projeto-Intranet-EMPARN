<?php
require_once __DIR__ . '/../config/connection.php';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Controle - EMPARN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">

    <style>
        body { background-color: #f4f6f8; margin: 0; font-family: 'Segoe UI', Arial, sans-serif; }
        .layout { display: flex; min-height: 100vh; }
        .main-content { flex: 1; padding: 40px 50px; margin-left: 250px; max-width: calc(100% - 250px); width: 100%; overflow-x: hidden; }
        @media (max-width: 768px) { .main-content { margin-left: 0; padding: 20px; } }
        h2 { font-size: 1.9rem; font-weight: 700; color: #003b82; margin-bottom: 25px; }
        .card-box { background: #fff; border-radius: 10px; padding: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); margin-bottom: 35px; border-left: 4px solid #0d6efd; max-width: 1700px; width: 100%; margin-left: auto; margin-right: auto; }
        .btn-acoes { display: flex; gap: 12px; flex-wrap: wrap; }
        footer { width: 100%; background: #e9ecef; padding: 15px; text-align: center; border-top: 1px solid #d1d1d1; margin-top: 40px; }
        #listaAcoes { max-height: 500px; overflow-y: auto; }
        .stat-card { padding: 20px; border-radius: 10px; color: #fff; box-shadow: 0 2px 10px rgba(0,0,0,0.1); height: 100%; display: flex; flex-direction: column; justify-content: center; }
        .stat-card h6 { font-weight: 600; margin-bottom: 5px; }
        .stat-card p { font-size: 2rem; font-weight: bold; margin: 0; }
    </style>
</head>
<body>

<?php include __DIR__ . '/../templates/gen_menu.php'; ?>

<main class="main-content">
    <h2><i class="bi bi-grid"></i> Painel de Controle</h2>

    <div class="card-box">
        <h5>Estatísticas Rápidas</h5>
        <div class="row text-center g-3">
            <div class="col-md-3"><div class="stat-card bg-success"><h6> Nº de Cards</h6><p><?php echo $pdo->query("SELECT COUNT(*) FROM dashboard")->fetchColumn(); ?></p></div></div>
            <div class="col-md-3"><div class="stat-card bg-primary"><h6>Nº de Notícias</h6><p><?php echo $pdo->query("SELECT COUNT(*) FROM noticias")->fetchColumn(); ?></p></div></div>
            <div class="col-md-3"><div class="stat-card bg-warning text-dark"><h6>Nº Usuários</h6><p><?php echo $pdo->query("SELECT COUNT(*) FROM usuario")->fetchColumn(); ?></p></div></div>
            <div class="col-md-3"><div class="stat-card bg-dark"><h6>Acessos Hoje</h6><p>89</p></div></div>
        </div>
    </div>

    <div class="card-box">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="mb-0">Últimas Ações</h5>
            <div class="d-flex gap-2 ms-3">
                <div class="form-check form-check-inline"><input class="form-check-input filtro-acao" type="checkbox" id="filtroTodos" value="TODOS" checked><label class="form-check-label" for="filtroTodos">Todos</label></div>
                <div class="form-check form-check-inline"><input class="form-check-input filtro-acao" type="checkbox" id="filtroCriacao" value="INSERIR"><label class="form-check-label" for="filtroCriacao">Criações</label></div>
                <div class="form-check form-check-inline"><input class="form-check-input filtro-acao" type="checkbox" id="filtroAtualizacao" value="ATUALIZAR"><label class="form-check-label" for="filtroAtualizacao">Atualizações</label></div>
                <div class="form-check form-check-inline"><input class="form-check-input filtro-acao" type="checkbox" id="filtroExclusao" value="EXCLUIR"><label class="form-check-label" for="filtroExclusao">Deleções</label></div>
                <div class="form-check form-check-inline"><input class="form-check-input filtro-acao" type="checkbox" id="filtroLogin" value="LOGIN"><label class="form-check-label" for="filtroLogin">Logins</label></div>
            </div>
        </div>

        <ul class="list-group" id="listaAcoes">
            <?php
            $stmt = $pdo->query("SELECT descricao, acao, data_acao FROM log_acao ORDER BY data_acao DESC");
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
    const ativo = document.querySelector('.filtro-acao:checked');
    const valor = ativo ? ativo.value : "TODOS";
    document.querySelectorAll('#listaAcoes .acao-item').forEach(item => {
        item.style.display = (valor === "TODOS" || item.dataset.acao === valor) ? '' : 'none';
    });
}

document.querySelectorAll('.filtro-acao').forEach(chk => {
    chk.addEventListener('change', () => {
        document.querySelectorAll('.filtro-acao').forEach(c => { if (c !== chk) c.checked = false; });
        if (!chk.checked) document.getElementById('filtroTodos').checked = true;
        aplicarFiltro();
    });
});

aplicarFiltro();
</script>
</body>
</html>
