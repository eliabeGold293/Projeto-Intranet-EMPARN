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
    exit;
}

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
        body {
            background-color: #f4f6f8;
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
        }

        .layout {
            display: flex;
            min-height: 100vh;
        }

        .main-content {
            flex: 1;
            padding: 40px 50px;
            margin-left: 250px;
            max-width: calc(100% - 250px);
            width: 100%;
            overflow-x: hidden;
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 20px;
            }
        }

        h2 {
            font-size: 1.9rem;
            font-weight: 700;
            color: #003b82;
            margin-bottom: 25px;
        }

        .card-box {
            background: #fff;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            margin-bottom: 35px;
            border-left: 4px solid #0d6efd;
            max-width: 1700px;
            width: 100%;
            margin-left: auto;
            margin-right: auto;
        }

        footer {
            width: 100%;
            background: #e9ecef;
            padding: 15px;
            text-align: center;
            border-top: 1px solid #d1d1d1;
            margin-top: 40px;
        }

        .stat-card {
            padding: 20px;
            border-radius: 10px;
            color: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .stat-card h6 {
            font-weight: 600;
            margin-bottom: 5px;
        }

        .stat-card p {
            font-size: 2rem;
            font-weight: bold;
            margin: 0;
        }

        .btn-circle-actions {
            width: 46px;
            height: 46px;
            border-radius: 50%;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
    </style>
</head>

<body>

    <?php include __DIR__ . '/../templates/gen_menu.php'; ?>

    <main class="main-content">

        <div class="dashboard-header mb-4">
            <h2 class="mb-0">
                <i class="bi bi-grid"></i> Painel de Controle
            </h2>

            <div class="dropdown">
                <button class="btn btn-primary btn-circle-actions"
                    type="button"
                    data-bs-toggle="dropdown"
                    title="Ações">
                    <i class="bi bi-gear-fill"></i>
                </button>

                <ul class="dropdown-menu dropdown-menu-end shadow">
                    <li>
                        <a class="dropdown-item" href="relatorio-acesso">
                            <i class="bi bi-bar-chart-line me-2"></i>
                            Relatórios de acesso
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="card-box">
            <h5>Estatísticas Rápidas</h5>
            <div class="row text-center g-3">
                <div class="col-md-3">
                    <div class="stat-card bg-success">
                        <h6>Nº de Cards</h6>
                        <p><?php echo $pdo->query("SELECT COUNT(*) FROM dashboard")->fetchColumn(); ?></p>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="stat-card bg-primary">
                        <h6>Nº de Notícias</h6>
                        <p><?php echo $pdo->query("SELECT COUNT(*) FROM noticias")->fetchColumn(); ?></p>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="stat-card bg-warning text-dark">
                        <h6>Nº Usuários</h6>
                        <p><?php echo $pdo->query("SELECT COUNT(*) FROM usuario")->fetchColumn(); ?></p>
                    </div>
                </div>

                <?php
                $timezone = new DateTimeZone('America/Sao_Paulo');
                $hoje = (new DateTime('now', $timezone))->format('Y-m-d');

                $stmtAcesso = $pdo->prepare("
                SELECT SUM(quantidade_login)
                FROM login_contador
                WHERE data_login = :data_login
            ");

                $stmtAcesso->execute([':data_login' => $hoje]);

                $acessosHoje = $stmtAcesso->fetchColumn() ?: 0;
                ?>

                <div class="col-md-3">
                    <div class="stat-card bg-dark">
                        <h6>Acessos Hoje</h6>
                        <p><?= htmlspecialchars($acessosHoje) ?></p>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">

            <!-- BOX 1: AÇÕES DO SISTEMA -->
            <div class="col-md-8">
                <div class="card-box">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Ações do Sistema</h5>

                        <div class="d-flex gap-2">
                            <select id="filtroAcao" class="form-select form-select-sm">
                                <option value="TODOS">Todos</option>
                                <option value="CREATE">Criações</option>
                                <option value="UPDATE">Atualizações</option>
                                <option value="DELETE">Deleções</option>
                            </select>
                        </div>
                    </div>

                    <ul class="list-group" id="listaAcoes" style="max-height: 400px; overflow-y:auto;">
                        <?php
                        $temRegistros = false;

                        $stmt = $pdo->query("
                            SELECT l.descricao, l.acao, l.data_acao, u.nome
                            FROM log_acao l
                            LEFT JOIN usuario u ON u.id = l.usuario_id
                            WHERE l.acao IN ('CREATE','UPDATE','DELETE')
                            ORDER BY l.data_acao DESC
                            LIMIT 100
                        ");

                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

                            $temRegistros = true;

                            $acaoLabel = match ($row['acao']) {
                                'CREATE' => 'Criação',
                                'UPDATE' => 'Atualização',
                                'DELETE' => 'Deleção',
                                default => $row['acao']
                            };

                            $badgeClass = match ($row['acao']) {
                                'CREATE' => 'bg-success',
                                'UPDATE' => 'bg-warning text-dark',
                                'DELETE' => 'bg-danger',
                                default => 'bg-secondary'
                            };

                            echo "
                            <li class='list-group-item acao-item d-flex justify-content-between align-items-start' data-acao='{$row['acao']}'>
                                <div>
                                    <strong>" . ($row['nome'] ?? 'Sistema') . "</strong>
                                    {$row['descricao']}
                                </div>

                                <div class='text-end'>
                                    <span class='badge {$badgeClass}'>{$acaoLabel}</span><br>
                                    <small class='text-muted'>" . date('d/m/Y H:i', strtotime($row['data_acao'])) . "</small>
                                </div>
                            </li>";
                        }

                        if (!$temRegistros) {
                            echo "<li class='list-group-item text-center text-muted'>Nenhum resultado encontrado</li>";
                        }
                        ?>
                    </ul>
                </div>
            </div>


            <!-- BOX 2: LOGINS -->
            <div class="col-md-4">
                <div class="card-box">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Logins Recentes</h5>

                        <select id="filtroLogin" class="form-select form-select-sm" style="width:150px;">
                            <option value="TODOS">Todos</option>
                            <option value="LOGIN">Logins</option>
                            <option value="LOGIN_PRIMEIRO_ACESSO">1º Acesso</option>
                        </select>
                    </div>

                    <ul class="list-group" id="listaLogins" style="max-height: 400px; overflow-y:auto;">
                        <?php
                        $temLogins = false;

                        $stmt = $pdo->query("
                            SELECT l.descricao, l.data_acao, l.acao, u.nome
                            FROM log_acao l
                            LEFT JOIN usuario u ON u.id = l.usuario_id
                            WHERE l.acao IN ('LOGIN', 'LOGIN_PRIMEIRO_ACESSO')
                            ORDER BY l.data_acao DESC
                            LIMIT 50
                        ");

                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

                            $temLogins = true;

                            $badgeClass = match ($row['acao']) {
                                'LOGIN' => 'bg-primary',
                                'LOGIN_PRIMEIRO_ACESSO' => 'bg-info text-dark',
                                default => 'bg-secondary'
                            };

                            $label = match ($row['acao']) {
                                'LOGIN' => 'Login',
                                'LOGIN_PRIMEIRO_ACESSO' => '1º Acesso',
                                default => $row['acao']
                            };

                            echo "
                            <li class='list-group-item login-item d-flex justify-content-between align-items-start' data-acao='{$row['acao']}'>
                                <div>
                                    <strong>" . ($row['nome'] ?? 'Sistema') . "</strong><br>
                                    <small>{$row['descricao']}</small><br>
                                    <span class='badge {$badgeClass}'>{$label}</span>
                                </div>

                                <small class='text-muted'>" . date('d/m H:i', strtotime($row['data_acao'])) . "</small>
                            </li>";
                        }

                        if (!$temLogins) {
                            echo "<li class='list-group-item text-center text-muted'>Nenhum resultado encontrado</li>";
                        }
                        ?>
                    </ul>
                </div>
            </div>

        </div>

    </main>

    <footer>
        <small>© <?= date('Y') ?> EMPARN - Painel de Controle</small>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        async function carregarAcoes(filtro = "TODOS") {
            const ul = document.getElementById('listaAcoes');
            ul.innerHTML = "<li class='list-group-item text-center'>Carregando...</li>";

            const res = await fetch(`ajax-filtro?tipo=acao&filtro=${filtro}`);
            const data = await res.json();

            ul.innerHTML = "";

            if (data.length === 0) {
                ul.innerHTML = "<li class='list-group-item text-center text-muted'>Nenhum resultado encontrado</li>";
                return;
            }

            data.forEach(row => {

                let badge = '';
                let label = '';

                if (row.acao === 'CREATE') {
                    badge = 'bg-success';
                    label = 'Criação';
                } else if (row.acao === 'UPDATE') {
                    badge = 'bg-warning text-dark';
                    label = 'Atualização';
                } else if (row.acao === 'DELETE') {
                    badge = 'bg-danger';
                    label = 'Deleção';
                }

                ul.innerHTML += `
        <li class='list-group-item d-flex justify-content-between align-items-start'>
            <div>
                <strong>${row.nome ?? 'Sistema'}</strong>
                ${row.descricao}
            </div>

            <div class='text-end'>
                <span class='badge ${badge}'>${label}</span><br>
                <small class='text-muted'>
                    ${new Date(row.data_acao).toLocaleString('pt-BR')}
                </small>
            </div>
        </li>`;
            });
        }


        async function carregarLogins(filtro = "TODOS") {
            const ul = document.getElementById('listaLogins');
            ul.innerHTML = "<li class='list-group-item text-center'>Carregando...</li>";

            const res = await fetch(`ajax-filtro?tipo=login&filtro=${filtro}`);
            const data = await res.json();

            ul.innerHTML = "";

            if (data.length === 0) {
                ul.innerHTML = "<li class='list-group-item text-center text-muted'>Nenhum resultado encontrado</li>";
                return;
            }

            data.forEach(row => {

                let badge = '';
                let label = '';

                if (row.acao === 'LOGIN') {
                    badge = 'bg-primary';
                    label = 'Login';
                } else if (row.acao === 'LOGIN_PRIMEIRO_ACESSO') {
                    badge = 'bg-info text-dark';
                    label = '1º Acesso';
                }

                ul.innerHTML += `
        <li class='list-group-item d-flex justify-content-between align-items-start'>
            <div>
                <strong>${row.nome ?? 'Sistema'}</strong><br>
                <small>${row.descricao}</small><br>
                <span class='badge ${badge}'>${label}</span>
            </div>

            <small class='text-muted'>
                ${new Date(row.data_acao).toLocaleString('pt-BR')}
            </small>
        </li>`;
            });
        }


        // EVENTOS
        document.getElementById('filtroAcao').addEventListener('change', function() {
            carregarAcoes(this.value);
        });

        document.getElementById('filtroLogin').addEventListener('change', function() {
            carregarLogins(this.value);
        });


        // CARREGAR AO ABRIR A PÁGINA
        carregarAcoes();
        carregarLogins();
    </script>

</body>

</html>