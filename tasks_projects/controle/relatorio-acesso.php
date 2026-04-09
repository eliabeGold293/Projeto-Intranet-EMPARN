<?php
require_once __DIR__ . '/../config/connection.php';

$data_inicio = $_GET['data_inicio'] ?? date('Y-01-01');
$data_fim    = $_GET['data_fim'] ?? date('Y-12-31');

// Total usuários
$sql_total = "SELECT COUNT(DISTINCT usuario_id) as total FROM login_contador WHERE data_login BETWEEN :inicio AND :fim";
$stmt = $pdo->prepare($sql_total);
$stmt->execute(['inicio' => $data_inicio, 'fim' => $data_fim]);
$total_usuarios = $stmt->fetch()['total'] ?? 0;

// Lista usuários
$sql_usuarios = "
SELECT u.id, u.nome, u.email, SUM(l.quantidade_login) as total_logins
FROM login_contador l
JOIN usuario u ON u.id = l.usuario_id
WHERE l.data_login BETWEEN :inicio AND :fim
GROUP BY u.id, u.nome, u.email
ORDER BY total_logins DESC
";
$stmt = $pdo->prepare($sql_usuarios);
$stmt->execute(['inicio' => $data_inicio, 'fim' => $data_fim]);
$usuarios = $stmt->fetchAll();

// TOP 5
$top5 = array_slice($usuarios, 0, 5);
$bottom5 = array_slice(array_reverse($usuarios), 0, 5);

// Média
$total_acessos = array_sum(array_column($usuarios, 'total_logins'));
$media_acessos = $total_usuarios > 0 ? round($total_acessos / $total_usuarios, 2) : 0;
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Relatório de Acessos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="bg-light">

    <div class="container py-4">

        <h3 class="mb-4"><i class="bi bi-bar-chart"></i> Relatório de Acessos</h3>

        <!-- FILTRO -->
        <form class="row g-3 mb-4">
            <div class="col-md-4">
                <label>Data início</label>
                <input type="date" name="data_inicio" class="form-control" value="<?= $data_inicio ?>">
            </div>

            <div class="col-md-4">
                <label>Data fim</label>
                <input type="date" name="data_fim" class="form-control" value="<?= $data_fim ?>">
            </div>

            <div class="col-md-4 d-flex align-items-end">
                <button class="btn btn-primary w-100">
                    <i class="bi bi-funnel"></i> Filtrar
                </button>
            </div>
        </form>

        <!-- CARDS -->
        <div class="row mb-4">

            <div class="col-md-3">
                <div class="card shadow h-100">
                    <div class="card-body">
                        <h5><i class="bi bi-people"></i> Usuários ativos</h5>
                        <h2><?= $total_usuarios ?></h2>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow border-primary h-100">
                    <div class="card-body">
                        <h5 class="text-primary">
                            <i class="bi bi-calculator"></i> Média de acessos
                        </h5>
                        <h2><?= $media_acessos ?> acessos</h2>
                    </div>
                </div>
            </div>

        </div>

        <!-- LISTA -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card shadow">
                    <div class="card-body">
                        <h5><i class="bi bi-list"></i> Usuários no período</h5>

                        <div style="max-height:300px;overflow:auto;">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nome</th>
                                        <th>Acessos</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($usuarios as $u): ?>
                                        <tr>
                                            <td><?= $u['id'] ?></td>
                                            <td><?= $u['nome'] ?></td>
                                            <td><?= $u['total_logins'] ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- TOPS -->
        <div class="row mb-4">

            <!-- TOP 5 MAIS -->
            <div class="col-md-6">
                <div class="card shadow border-success h-100">
                    <div class="card-body">
                        <h5 class="text-success">
                            <i class="bi bi-trophy"></i> Top 5 que mais acessaram
                        </h5>

                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nome</th>
                                    <th>Acessos</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($top5 as $index => $u): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td><?= $u['nome'] ?></td>
                                        <td><?= $u['total_logins'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>

            <!-- TOP 5 MENOS -->
            <div class="col-md-6">
                <div class="card shadow border-danger h-100">
                    <div class="card-body">
                        <h5 class="text-danger">
                            <i class="bi bi-emoji-frown"></i> Top 5 que menos acessaram
                        </h5>

                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nome</th>
                                    <th>Acessos</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bottom5 as $index => $u): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td><?= $u['nome'] ?></td>
                                        <td><?= $u['total_logins'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>

        </div>

        <!-- GRÁFICO -->
        <div class="card shadow mb-4">
            <div class="card-body">
                <h5><i class="bi bi-graph-up"></i> Acessos por usuário</h5>
                <div style="overflow-x:auto;">
                    <div id="grafico-container" style="min-width:600px; height:450px;">
                        <canvas id="grafico"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- BOTÕES -->
        <div class="d-flex gap-2">
            <a href="gerar_pdf?inicio=<?= $data_inicio ?>&fim=<?= $data_fim ?>" class="btn btn-danger">
                <i class="bi bi-file-earmark-pdf"></i> PDF ABNT
            </a>

            <button class="btn btn-success" onclick="gerarExcel()">
                <i class="bi bi-file-earmark-excel"></i> Excel
            </button>
        </div>

    </div>

    <script>
        const usuarios = <?= json_encode($usuarios) ?>;
        const labels = usuarios.map(u => u.nome);
        const dados = usuarios.map(u => u.total_logins);

        // largura dinâmica (cada usuário = 100px)
        const largura = usuarios.length * 100;

        // aplica largura no container (scroll)
        document.getElementById('grafico-container').style.minWidth = largura + 'px';

        // AJUSTE REAL DO TAMANHO DO GRÁFICO
        const canvas = document.getElementById('grafico');
        canvas.width = largura;
        canvas.height = 450;

        // CRIA GRÁFICO
        new Chart(canvas, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Acessos',
                    data: dados
                }]
            },
            options: {
                responsive: false,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        ticks: {
                            autoSkip: false,
                            maxRotation: 45,
                            minRotation: 45
                        }
                    },
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // ===== EXCEL =====
        function gerarExcel() {

            let csv = "ID,Nome,Email,Acessos\n";

            usuarios.forEach(u => {
                csv += `${u.id},${u.nome},${u.email},${u.total_logins}\n`;
            });

            // FORMATAR DATAS
            const inicio = "<?= $data_inicio ?>";
            const fim = "<?= $data_fim ?>";

            function formatarData(data) {
                const [ano, mes, dia] = data.split("-");
                return `${dia}-${mes}-${ano}`; // formato seguro
            }

            const inicioFormatado = formatarData(inicio);
            const fimFormatado = formatarData(fim);

            const nomeArquivo = `relatorio_acessos_${inicioFormatado}_a_${fimFormatado}.csv`;

            // DOWNLOAD
            let blob = new Blob(["\uFEFF" + csv], {
                type: 'text/csv;charset=utf-8;'
            });

            let link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = nomeArquivo;
            link.click();
        }
    </script>

</body>

</html>