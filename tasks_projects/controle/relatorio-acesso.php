<?php
session_start();

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

$ano = $_GET['ano'] ?? date("Y");
$usuarioFiltro = $_GET['usuario'] ?? '';
$areaFiltro = $_GET['area'] ?? '';
$dataInicio = $_GET['data_inicio'] ?? '';
$dataFim = $_GET['data_fim'] ?? '';

$where = " WHERE 1=1 ";
$params = [];

if ($ano) {
    $where .= " AND EXTRACT(YEAR FROM lc.data_login) = :ano ";
    $params[':ano'] = $ano;
}

if (!empty($usuarioFiltro)) {
    $where .= " AND u.id = :usuario ";
    $params[':usuario'] = $usuarioFiltro;
}

if (!empty($areaFiltro)) {
    $where .= " AND u.area_id = :area ";
    $params[':area'] = $areaFiltro;
}

if (!empty($dataInicio)) {
    $where .= " AND lc.data_login >= :inicio ";
    $params[':inicio'] = $dataInicio;
}

if (!empty($dataFim)) {
    $where .= " AND lc.data_login <= :fim ";
    $params[':fim'] = $dataFim;
}

if ($usuarioFiltro) {
    $where .= " AND u.id = :usuario ";
    $params[':usuario'] = $usuarioFiltro;
}

if ($areaFiltro) {
    $where .= " AND u.area_id = :area ";
    $params[':area'] = $areaFiltro;
}

if ($dataInicio) {
    $where .= " AND lc.data_login >= :inicio ";
    $params[':inicio'] = $dataInicio;
}

if ($dataFim) {
    $where .= " AND lc.data_login <= :fim ";
    $params[':fim'] = $dataFim;
}

/* =======================
CARDS DASHBOARD
=======================*/

$sqlAno = "SELECT SUM(quantidade_login) total FROM login_contador 
WHERE EXTRACT(YEAR FROM data_login)=:ano";

$stmt = $pdo->prepare($sqlAno);
$stmt->execute([':ano' => $ano]);
$totalAno = $stmt->fetchColumn() ?? 0;


$sqlMes = "SELECT SUM(quantidade_login)
FROM login_contador
WHERE DATE_TRUNC('month',data_login)=DATE_TRUNC('month',CURRENT_DATE)";
$totalMes = $pdo->query($sqlMes)->fetchColumn() ?? 0;


$sqlUsuariosAtivos = "
SELECT COUNT(DISTINCT usuario_id)
FROM login_contador
WHERE EXTRACT(YEAR FROM data_login)=:ano";
$stmt = $pdo->prepare($sqlUsuariosAtivos);
$stmt->execute([':ano' => $ano]);
$usuariosAtivos = $stmt->fetchColumn();


$mediaDiaria = $totalAno > 0 ? round($totalAno / 365, 2) : 0;


/* =======================
ACESSOS POR MÊS
=======================*/

$sqlMeses = "
SELECT 
EXTRACT(MONTH FROM data_login) mes,
SUM(quantidade_login) acessos
FROM login_contador
WHERE EXTRACT(YEAR FROM data_login)=:ano
GROUP BY mes
ORDER BY mes";

$stmt = $pdo->prepare($sqlMeses);
$stmt->execute([':ano' => $ano]);
$dadosMeses = $stmt->fetchAll(PDO::FETCH_ASSOC);

$labelsMes = [];
$dadosMes = [];

$nomesMeses = [
    1 => 'Janeiro',
    2 => 'Fevereiro',
    3 => 'Março',
    4 => 'Abril',
    5 => 'Maio',
    6 => 'Junho',
    7 => 'Julho',
    8 => 'Agosto',
    9 => 'Setembro',
    10 => 'Outubro',
    11 => 'Novembro',
    12 => 'Dezembro'
];

foreach ($dadosMeses as $m) {

    $mesNumero = (int)$m['mes'];

    $labelsMes[] = $nomesMeses[$mesNumero];

    $dadosMes[] = $m['acessos'];
}


/* =======================
ACESSOS POR DIA
=======================*/

$sqlDias = "
SELECT DATE(data_login) dia,
SUM(quantidade_login) acessos
FROM login_contador
WHERE EXTRACT(YEAR FROM data_login)=:ano
GROUP BY dia
ORDER BY dia";

$stmt = $pdo->prepare($sqlDias);
$stmt->execute([':ano' => $ano]);
$dias = $stmt->fetchAll(PDO::FETCH_ASSOC);

$labelsDia = [];
$dadosDia = [];

foreach ($dias as $d) {
    $labelsDia[] = date('d/m/Y', strtotime($d['dia']));
    $dadosDia[] = $d['acessos'];
}


/* =======================
ACESSOS POR USUÁRIO
=======================*/

$sqlUsuarios = "
SELECT 
u.nome,
SUM(lc.quantidade_login) total
FROM login_contador lc
JOIN usuario u ON u.id=lc.usuario_id
$where
GROUP BY u.nome
ORDER BY total DESC";

$stmt = $pdo->prepare($sqlUsuarios);
$stmt->execute($params);
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

$semResultados = count($usuarios) === 0;


/* =======================
LISTA USUARIOS
=======================*/

$listaUsuarios = $pdo->query("SELECT id,nome FROM usuario ORDER BY nome")->fetchAll();
$areas = $pdo->query("SELECT id,nome FROM area_atuacao")->fetchAll();

$sqlExport = "
SELECT
u.id AS usuario_id,
u.nome AS usuario,
u.email AS email,
a.nome AS area,
DATE(lc.data_login) AS data_acesso,
SUM(lc.quantidade_login) AS quantidade_acessos
FROM login_contador lc
JOIN usuario u ON u.id = lc.usuario_id
LEFT JOIN area_atuacao a ON a.id = u.area_id
$where
GROUP BY u.id,u.nome,u.email,a.nome,data_acesso
ORDER BY data_acesso DESC
";

$stmt = $pdo->prepare($sqlExport);
$stmt->execute($params);
$dadosExport = $stmt->fetchAll(PDO::FETCH_ASSOC);

$nomeUsuarioFiltro = 'Todos';

if ($usuarioFiltro) {
    foreach ($listaUsuarios as $u) {
        if ($u['id'] == $usuarioFiltro) {
            $nomeUsuarioFiltro = $u['nome'];
        }
    }
}

$nomeAreaFiltro = 'Todas';

if ($areaFiltro) {
    foreach ($areas as $a) {
        if ($a['id'] == $areaFiltro) {
            $nomeAreaFiltro = $a['nome'];
        }
    }
}

$mesesNomes = [
    1 => 'Janeiro',
    2 => 'Fevereiro',
    3 => 'Março',
    4 => 'Abril',
    5 => 'Maio',
    6 => 'Junho',
    7 => 'Julho',
    8 => 'Agosto',
    9 => 'Setembro',
    10 => 'Outubro',
    11 => 'Novembro',
    12 => 'Dezembro'
];

$mesEmissao = $mesesNomes[(int)date('n')];
$dataEmissao = date('d/m/Y H:i');
$anoRelatorio = $ano;
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">
    <title>Dashboard de Acessos</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body {
            background: #f4f6f9;
        }

        .card-dashboard {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }

        .metric {
            font-size: 28px;
            font-weight: 700;
        }
    </style>

</head>

<body>

    <div class="container-fluid mt-4">

        <?php if ($semResultados) { ?>

            <div class="alert alert-warning mt-3">

                ⚠️ Não foi possível encontrar dados com os filtros selecionados.

            </div>

        <?php } ?>

        <h3 class="mb-4">📊 Dashboard de Acessos</h3>

        <form class="row g-2 mb-4">

            <div class="col-md-2">
                <select name="ano" class="form-select">
                    <?php
                    for ($i = date("Y"); $i >= date("Y") - 5; $i--) {
                        $sel = $i == $ano ? 'selected' : '';
                        echo "<option $sel>$i</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="col-md-3">
                <select name="usuario" class="form-select">
                    <option value="">Todos usuários</option>
                    <?php
                    foreach ($listaUsuarios as $u) {
                        $sel = $usuarioFiltro == $u['id'] ? 'selected' : '';
                        echo "<option value='{$u['id']}' $sel>{$u['nome']}</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="col-md-3">
                <select name="area" class="form-select">
                    <option value="">Todas áreas</option>
                    <?php
                    foreach ($areas as $a) {
                        $sel = $areaFiltro == $a['id'] ? 'selected' : '';
                        echo "<option value='{$a['id']}' $sel>{$a['nome']}</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="col-md-2">
                <input type="date" name="data_inicio" class="form-control" value="<?= $dataInicio ?>">
            </div>

            <div class="col-md-2">
                <input type="date" name="data_fim" class="form-control" value="<?= $dataFim ?>">
            </div>

            <div class="col-md-2 mt-2">
                <button class="btn btn-primary w-100">Filtrar</button>
            </div>

        </form>

        <div class="row mb-4">

            <div class="col-md-3">
                <div class="card card-dashboard p-3">
                    <div>Acessos no ano</div>
                    <div class="metric"><?= $totalAno ?></div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card card-dashboard p-3">
                    <div>Acessos este mês</div>
                    <div class="metric"><?= $totalMes ?></div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card card-dashboard p-3">
                    <div>Usuários ativos</div>
                    <div class="metric"><?= $usuariosAtivos ?></div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card card-dashboard p-3">
                    <div>Média diária</div>
                    <div class="metric"><?= $mediaDiaria ?></div>
                </div>
            </div>

        </div>


        <div class="row">

            <div class="col-md-6">

                <div class="card card-dashboard p-4">

                    <h5>Acessos por mês</h5>

                    <canvas id="graficoMes"></canvas>

                </div>

            </div>

            <div class="col-md-6">

                <div class="card card-dashboard p-4">

                    <h5>Acessos por dia</h5>

                    <canvas id="graficoDia"></canvas>

                </div>

            </div>

        </div>


        <div class="card card-dashboard p-4 mt-4">

            <h5>Ranking de usuários</h5>

            <table class="table table-striped">

                <thead>
                    <tr>
                        <th>Posição</th>
                        <th>Usuário</th>
                        <th>Acessos</th>
                    </tr>
                </thead>

                <tbody>

                    <?php
                    $posicao = 1;

                    foreach ($usuarios as $u) {
                    ?>

                        <tr>

                            <td>
                                <?php
                                if ($posicao == 1) echo "🥇";
                                elseif ($posicao == 2) echo "🥈";
                                elseif ($posicao == 3) echo "🥉";
                                else echo $posicao;
                                ?>
                            </td>

                            <td><?= $u['nome'] ?></td>

                            <td><?= $u['total'] ?></td>

                        </tr>

                    <?php
                        $posicao++;
                    }
                    ?>

                </tbody>

            </table>

            <button onclick="exportExcel()" class="btn btn-success">Exportar Excel</button>
            <button onclick="exportPDF()" class="btn btn-danger">Exportar PDF</button>

        </div>

    </div>

    <div id="tabelaExportacao" style="display:none">

        <table border="1">

            <tr>
                <th>ID_Usuario</th>
                <th>Usuario</th>
                <th>Email</th>
                <th>Area</th>
                <th>Data_Acesso</th>
                <th>Quantidade_Acessos</th>
            </tr>
            <?php foreach ($dadosExport as $d) { ?>

                <tr>

                    <td><?= $d['usuario_id'] ?></td>
                    <td><?= $d['usuario'] ?></td>
                    <td><?= $d['email'] ?></td>
                    <td><?= $d['area'] ?></td>
                    <td><?= $d['data_acesso'] ?></td>
                    <td><?= $d['quantidade_acessos'] ?></td>

                </tr>

            <?php } ?>

        </table>

    </div>

    <div id="relatorioPDF" style="display:none">

        <h2>Relatório de Acessos ao Sistema</h2>

        <p style="text-align:center">
            Relatório analítico de utilização da plataforma
        </p>

        <h4>Informações do relatório</h4>

        <table border="1" cellpadding="6">

            <tr>
                <td><b>Período analisado</b></td>
                <td>Ano de <?= $anoRelatorio ?></td>
            </tr>

            <tr>
                <td><b>Mês de emissão</b></td>
                <td><?= $mesEmissao ?></td>
            </tr>

            <tr>
                <td><b>Data de emissão</b></td>
                <td><?= $dataEmissao ?></td>
            </tr>

        </table>

        <br>

        <hr>

        <h4>Filtros aplicados</h4>

        <table border="1" cellpadding="6">

            <tr>
                <td><b>Ano</b></td>
                <td><?= $ano ?></td>
            </tr>

            <tr>
                <td><b>Usuário</b></td>
                <td><?= $nomeUsuarioFiltro ?></td>
            </tr>

            <tr>
                <td><b>Área</b></td>
                <td><?= $nomeAreaFiltro ?></td>
            </tr>

            <tr>
                <td><b>Data inicial</b></td>
                <td><?= $dataInicio ?: 'Não informado' ?></td>
            </tr>

            <tr>
                <td><b>Data final</b></td>
                <td><?= $dataFim ?: 'Não informado' ?></td>
            </tr>

        </table>

        <br>

        <h4>Resumo de acessos</h4>

        <table border="1" cellpadding="6">

            <tr>
                <th>Total acessos no ano</th>
                <th>Acessos este mês</th>
                <th>Usuários ativos</th>
                <th>Média diária</th>
            </tr>

            <tr>
                <td><?= $totalAno ?></td>
                <td><?= $totalMes ?></td>
                <td><?= $usuariosAtivos ?></td>
                <td><?= $mediaDiaria ?></td>
            </tr>

        </table>

        <br>

        <h4>Acessos por mês</h4>

        <table border="1" cellpadding="6">

            <tr>
                <th>Mês</th>
                <th>Quantidade de acessos</th>
            </tr>

            <?php
            foreach ($dadosMeses as $m) {
            ?>

                <tr>
                    <td><?= $nomesMeses[(int)$m['mes']] ?? '' ?></td>
                    <td><?= $m['acessos'] ?? '' ?></td>
                </tr>

            <?php } ?>

        </table>

        <br>

        <h4>Ranking de usuários</h4>

        <table border="1" cellpadding="6">

            <thead>
                <tr>
                    <th>Posição</th>
                    <th>Usuário</th>
                    <th>Acessos</th>
                </tr>
            </thead>

            <tbody>

                <?php
                $posicao = 1;

                foreach ($usuarios as $u) {
                ?>

                    <tr>

                        <td><b><?= $posicao ?></b></td>

                        <td><?= $u['nome'] ?></td>

                        <td><?= $u['total'] ?></td>

                    </tr>

                <?php
                    $posicao++;
                }
                ?>

            </tbody>
        </table>

    </div>


    <script>
        new Chart(document.getElementById('graficoMes'), {
            type: 'bar',
            data: {
                labels: <?= json_encode($labelsMes) ?>,
                datasets: [{
                    label: 'Quantidade de acessos',
                    data: <?= json_encode($dadosMes) ?>,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true
                    }
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Mês do ano'
                        }
                    },
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Número de acessos'
                        }
                    }
                }
            }
        });

        new Chart(document.getElementById('graficoDia'), {
            type: 'line',
            data: {
                labels: <?= json_encode($labelsDia) ?>,
                datasets: [{
                    label: 'Quantidade de acessos',
                    data: <?= json_encode($dadosDia) ?>,
                    tension: 0.3,
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true
                    }
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Dia'
                        }
                    },
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Número de acessos'
                        }
                    }
                }
            }
        });

        function exportExcel() {

            let tabela = document.getElementById("tabelaExportacao").outerHTML;

            let html = `
                <html>
                <head>
                <meta charset="UTF-8">
                </head>
                <body>
                ${tabela}
                </body>
                </html>
            `;

            let url = 'data:application/vnd.ms-excel,' + encodeURIComponent(html);

            let link = document.createElement('a');

            link.href = url;

            link.download = "dados_acessos.xls";

            link.click();

        }

        function exportPDF() {

            let conteudo = document.getElementById("relatorioPDF").innerHTML;

            let janela = window.open('', '', 'width=900,height=700');

            janela.document.write(`

                <html>
                <head>
                <title>Relatório de Acessos</title>

                <style>

                body{
                font-family:Arial;
                padding:30px;
                }

                h2{
                text-align:center;
                }

                table{
                width:100%;
                border-collapse:collapse;
                margin-top:10px;
                }

                th,td{
                border:1px solid black;
                padding:8px;
                text-align:left;
                }

                </style>

                </head>

                <body>

                ${conteudo}

                </body>
                </html>
            `);

            janela.document.close();
            janela.print();

        }
    </script>

</body>

</html>