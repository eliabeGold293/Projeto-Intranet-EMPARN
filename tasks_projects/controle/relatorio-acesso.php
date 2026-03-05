<?php
require_once __DIR__ . '/../config/connection.php';

$acao = $_GET['acao'] ?? null;

/*
=============================
TOTAIS PARA OS CARDS
=============================
*/

$totalDia = $pdo->query("
SELECT COALESCE(SUM(quantidade_login),0)
FROM login_contador
WHERE DATE(data_login) = CURRENT_DATE
")->fetchColumn();

$totalSemana = $pdo->query("
SELECT COALESCE(SUM(quantidade_login),0)
FROM login_contador
WHERE DATE_TRUNC('week', data_login) = DATE_TRUNC('week', CURRENT_DATE)
")->fetchColumn();

$totalMes = $pdo->query("
SELECT COALESCE(SUM(quantidade_login),0)
FROM login_contador
WHERE DATE_TRUNC('month', data_login) = DATE_TRUNC('month', CURRENT_DATE)
")->fetchColumn();

$totalAno = $pdo->query("
SELECT COALESCE(SUM(quantidade_login),0)
FROM login_contador
WHERE DATE_TRUNC('year', data_login) = DATE_TRUNC('year', CURRENT_DATE)
")->fetchColumn();

/*
=============================
RELATÓRIO DIÁRIO
=============================
*/

$stmtDia = $pdo->prepare("
SELECT
    u.nome,
    u.email,
    l.data_login,
    l.quantidade_login
FROM login_contador l
LEFT JOIN usuario u ON u.id = l.usuario_id
WHERE DATE(l.data_login) = CURRENT_DATE
ORDER BY u.nome
");

$stmtDia->execute();
$dadosDia = $stmtDia->fetchAll(PDO::FETCH_ASSOC);

/*
=============================
RELATÓRIO SEMANAL
=============================
*/

$inicioSemana = date('Y-m-d', strtotime('monday this week'));
$fimSemana = date('Y-m-d', strtotime('sunday this week'));

$stmt = $pdo->prepare("
SELECT
    u.nome,
    l.data_login,
    l.quantidade_login
FROM login_contador l
LEFT JOIN usuario u ON u.id = l.usuario_id
WHERE l.data_login BETWEEN :inicio AND :fim
ORDER BY u.nome, l.data_login
");

$stmt->execute([
    ':inicio' => $inicioSemana,
    ':fim' => $fimSemana
]);

$dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

$usuarios = [];

foreach ($dados as $d) {

    $nome = $d['nome'] ?? 'Usuário removido';
    $data = $d['data_login'];
    $acessos = $d['quantidade_login'];

    $usuarios[$nome][$data] = $acessos;
}

$tituloSemana = date('F Y', strtotime($inicioSemana));

$dias = [];

$nomesDias = ['S', 'T', 'Q', 'Q', 'S', 'S', 'D'];

for ($i = 0; $i < 7; $i++) {

    $data = date('Y-m-d', strtotime("$inicioSemana +$i days"));

    $dias[] = [
        'data' => $data,
        'label' => $nomesDias[$i],
        'data_formatada' => date('d/m', strtotime($data))
    ];
}

/*
=============================
RELATÓRIO MENSAL
=============================
*/

$stmtMes = $pdo->prepare("
SELECT
    u.nome,
    u.email,
    l.data_login,
    l.quantidade_login
FROM login_contador l
LEFT JOIN usuario u ON u.id = l.usuario_id
WHERE DATE_TRUNC('month', l.data_login) = DATE_TRUNC('month', CURRENT_DATE)
ORDER BY l.data_login DESC
");

$stmtMes->execute();
$dadosMes = $stmtMes->fetchAll(PDO::FETCH_ASSOC);

/*
=============================
RELATÓRIO ANUAL
=============================
*/

$mesSelecionado = $_GET['mes'] ?? date('m');

/* usuários selecionados no filtro */

$usuarioSelecionado = $_GET['usuario'] ?? null;

/* lista de usuários para o filtro */

$stmtUsuarios = $pdo->query("
SELECT id, nome, email
FROM usuario
ORDER BY email
");

$listaUsuarios = $stmtUsuarios->fetchAll(PDO::FETCH_ASSOC);

$dadosUsuariosFiltro = [];

if (!empty($usuarioSelecionado)) {

    $stmtFiltroUsuarios = $pdo->prepare("
        SELECT
            u.nome,
            u.email,
            l.data_login,
            l.quantidade_login
        FROM login_contador l
        LEFT JOIN usuario u ON u.id = l.usuario_id
        WHERE u.email = :email
        ORDER BY l.data_login DESC
    ");

    $stmtFiltroUsuarios->execute([
        ':email' => $usuarioSelecionado
    ]);

    $dadosUsuariosFiltro = $stmtFiltroUsuarios->fetchAll(PDO::FETCH_ASSOC);
}

/* dados por mês */

$stmtAno = $pdo->prepare("
SELECT
    u.nome,
    SUM(CASE WHEN EXTRACT(MONTH FROM l.data_login)=1 THEN l.quantidade_login ELSE 0 END) AS jan,
    SUM(CASE WHEN EXTRACT(MONTH FROM l.data_login)=2 THEN l.quantidade_login ELSE 0 END) AS fev,
    SUM(CASE WHEN EXTRACT(MONTH FROM l.data_login)=3 THEN l.quantidade_login ELSE 0 END) AS mar,
    SUM(CASE WHEN EXTRACT(MONTH FROM l.data_login)=4 THEN l.quantidade_login ELSE 0 END) AS abr,
    SUM(CASE WHEN EXTRACT(MONTH FROM l.data_login)=5 THEN l.quantidade_login ELSE 0 END) AS mai,
    SUM(CASE WHEN EXTRACT(MONTH FROM l.data_login)=6 THEN l.quantidade_login ELSE 0 END) AS jun,
    SUM(CASE WHEN EXTRACT(MONTH FROM l.data_login)=7 THEN l.quantidade_login ELSE 0 END) AS jul,
    SUM(CASE WHEN EXTRACT(MONTH FROM l.data_login)=8 THEN l.quantidade_login ELSE 0 END) AS ago,
    SUM(CASE WHEN EXTRACT(MONTH FROM l.data_login)=9 THEN l.quantidade_login ELSE 0 END) AS set,
    SUM(CASE WHEN EXTRACT(MONTH FROM l.data_login)=10 THEN l.quantidade_login ELSE 0 END) AS out,
    SUM(CASE WHEN EXTRACT(MONTH FROM l.data_login)=11 THEN l.quantidade_login ELSE 0 END) AS nov,
    SUM(CASE WHEN EXTRACT(MONTH FROM l.data_login)=12 THEN l.quantidade_login ELSE 0 END) AS dez
FROM login_contador l
LEFT JOIN usuario u ON u.id = l.usuario_id
WHERE DATE_TRUNC('year', l.data_login) = DATE_TRUNC('year', CURRENT_DATE)
GROUP BY u.nome
ORDER BY u.nome
");

$stmtAno->execute();
$dadosAno = $stmtAno->fetchAll(PDO::FETCH_ASSOC);

/* dados filtrados por mês */

$stmtMesFiltro = $pdo->prepare("
SELECT
    u.nome,
    u.email,
    l.data_login,
    l.quantidade_login
FROM login_contador l
LEFT JOIN usuario u ON u.id = l.usuario_id
WHERE DATE_TRUNC('year', l.data_login)=DATE_TRUNC('year', CURRENT_DATE)
AND EXTRACT(MONTH FROM l.data_login)=:mes
ORDER BY l.data_login
");

$stmtMesFiltro->execute([
    ':mes' => $mesSelecionado
]);

$dadosMesFiltro = $stmtMesFiltro->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">
    <title>Relatório de Acessos</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <style>
        body {
            background: #f3f5f9;
            font-family: Segoe UI;
        }

        .card-dashboard {
            cursor: pointer;
            transition: .2s;
        }

        .card-dashboard:hover {
            transform: scale(1.03);
        }

        .tabela-scroll {

            max-height: 350px;
            overflow-y: auto;
            overflow-x: auto;

        }
    </style>

</head>

<body>

    <div class="container mt-4">

        <div class="mb-3">
            <a href="javascript:history.back();" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>

        <h3 class="mb-4">Relatório de Acessos</h3>

        <!-- =========================
CARDS
========================= -->

        <div class="row g-3 mb-5">

            <div class="col-md-3">
                <div class="card card-dashboard text-center">
                    <div class="card-body">
                        <h6>Acessos Hoje</h6>
                        <h2><?= $totalDia ?></h2>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card card-dashboard text-center bg-primary text-white">
                    <div class="card-body">
                        <h6>Acessos da Semana</h6>
                        <h2><?= $totalSemana ?></h2>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card card-dashboard text-center">
                    <div class="card-body">
                        <h6>Acessos do Mês</h6>
                        <h2><?= $totalMes ?></h2>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card card-dashboard text-center">
                    <div class="card-body">
                        <h6>Acessos do Ano</h6>
                        <h2><?= $totalAno ?></h2>
                    </div>
                </div>
            </div>

        </div>

        <!-- =========================
RELATÓRIO DIÁRIO
========================= -->

        <div class="card mb-5" id="relatorio-anual">

            <div class="card-body">

                <h4 class="mb-4">Relatório Diário</h4>

                <div class="card bg-success text-white mb-3" style="width:220px">

                    <div class="card-body text-center">

                        Total de acessos hoje

                        <h3><?= $totalDia ?></h3>

                    </div>

                </div>

                <div class="table-responsive">

                    <table class="table table-bordered">

                        <thead class="table-dark">

                            <tr>
                                <th>Nome</th>
                                <th>Email</th>
                                <th>Acessos no dia</th>
                                <th>Hora do acesso</th>
                            </tr>

                        </thead>

                        <tbody>

                            <?php foreach ($dadosDia as $d): ?>

                                <tr>

                                    <td><?= $d['nome'] ?></td>
                                    <td><?= $d['email'] ?></td>
                                    <td class="text-center"><?= $d['quantidade_login'] ?></td>
                                    <td class="text-center">
                                        <?= date('H:i:s', strtotime($d['data_login'])) ?>
                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

        <!-- =========================
RELATÓRIO SEMANAL
========================= -->

        <div class="card mb-5">

            <div class="card-body">

                <h4 class="mb-4">

                    Relatório Semanal — <?= ucfirst($tituloSemana) ?>

                </h4>

                <div class="mb-3">

                    <div class="card bg-success text-white" style="width:200px">

                        <div class="card-body text-center">

                            Acessos da semana

                            <h3><?= $totalSemana ?></h3>

                        </div>

                    </div>

                </div>

                <div class="table-responsive">

                    <table class="table table-bordered">

                        <thead class="table-dark">

                            <tr>

                                <th>Usuário</th>

                                <?php foreach ($dias as $d): ?>

                                    <th class="text-center">

                                        <?= $d['label'] ?><br>

                                        <small><?= $d['data_formatada'] ?></small>

                                    </th>

                                <?php endforeach; ?>

                            </tr>

                        </thead>

                        <tbody>

                            <?php foreach ($usuarios as $nome => $dadosUsuario): ?>

                                <tr>

                                    <td><?= $nome ?></td>

                                    <?php foreach ($dias as $d): ?>

                                        <td class="text-center">

                                            <?= $dadosUsuario[$d['data']] ?? '-' ?>

                                        </td>

                                    <?php endforeach; ?>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

        <!-- =========================
RELATÓRIO MENSAL
========================= -->

        <div class="card mb-5">

            <div class="card-body">

                <h4 class="mb-4">Relatório Mensal</h4>

                <div class="card bg-primary text-white mb-3" style="width:220px">

                    <div class="card-body text-center">

                        Total de acessos no mês

                        <h3><?= $totalMes ?></h3>

                    </div>

                </div>

                <div class="table-responsive">

                    <table class="table table-bordered">

                        <thead class="table-dark">

                            <tr>

                                <th>Nome</th>
                                <th>Email</th>
                                <th>Data do acesso</th>
                                <th>Quantidade</th>

                            </tr>

                        </thead>

                        <tbody>

                            <?php foreach ($dadosMes as $d): ?>

                                <tr>

                                    <td><?= $d['nome'] ?></td>
                                    <td><?= $d['email'] ?></td>
                                    <td><?= date('d/m/Y', strtotime($d['data_login'])) ?></td>
                                    <td class="text-center"><?= $d['quantidade_login'] ?></td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

        <!-- =========================
RELATÓRIO ANUAL
========================= -->

        <div class="card mb-5">

            <div class="card-body">

                <h4 class="mb-4">Relatório Anual</h4>

                <div class="card bg-dark text-white mb-3" style="width:220px">

                    <div class="card-body text-center">

                        Total de acessos no ano

                        <h3><?= $totalAno ?></h3>

                    </div>

                </div>

                <div class="table-responsive mb-4 tabela-scroll">

                    <table class="table table-bordered">

                        <thead class="table-dark">

                            <tr>

                                <th>Usuário</th>
                                <th>Jan</th>
                                <th>Fev</th>
                                <th>Mar</th>
                                <th>Abr</th>
                                <th>Mai</th>
                                <th>Jun</th>
                                <th>Jul</th>
                                <th>Ago</th>
                                <th>Set</th>
                                <th>Out</th>
                                <th>Nov</th>
                                <th>Dez</th>

                            </tr>

                        </thead>

                        <tbody>

                            <?php foreach ($dadosAno as $d): ?>

                                <tr>

                                    <td><?= $d['nome'] ?></td>
                                    <td><?= $d['jan'] ?></td>
                                    <td><?= $d['fev'] ?></td>
                                    <td><?= $d['mar'] ?></td>
                                    <td><?= $d['abr'] ?></td>
                                    <td><?= $d['mai'] ?></td>
                                    <td><?= $d['jun'] ?></td>
                                    <td><?= $d['jul'] ?></td>
                                    <td><?= $d['ago'] ?></td>
                                    <td><?= $d['set'] ?></td>
                                    <td><?= $d['out'] ?></td>
                                    <td><?= $d['nov'] ?></td>
                                    <td><?= $d['dez'] ?></td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

                <div class="card mb-4 border-primary">

                    <div class="card-header bg-primary text-white">

                        <b>Filtros do relatório</b>

                    </div>

                    <div class="card-body">

                        <!-- FILTRO MÊS -->

                        <form method="GET" action="#relatorio-anual" class="mb-3">

                            <label class="form-label"><b>Filtrar mês</b></label>

                            <select name="mes" class="form-select" onchange="this.form.submit()">

                                <?php

                                $meses = [
                                    1 => "Janeiro",
                                    2 => "Fevereiro",
                                    3 => "Março",
                                    4 => "Abril",
                                    5 => "Maio",
                                    6 => "Junho",
                                    7 => "Julho",
                                    8 => "Agosto",
                                    9 => "Setembro",
                                    10 => "Outubro",
                                    11 => "Novembro",
                                    12 => "Dezembro"
                                ];

                                foreach ($meses as $num => $nome) {

                                    $selected = ($num == $mesSelecionado) ? 'selected' : '';

                                    echo "<option value='$num' $selected>$nome</option>";
                                }

                                ?>

                            </select>

                        </form>

                        <h5 class="mt-4">Filtrar usuários</h5>

                        <form method="GET" action="#relatorio-anual" class="mb-4">

<label class="form-label"><b>Selecionar usuário</b></label>

<select name="usuario" class="form-select" onchange="this.form.submit()">

<option value="">Selecione um usuário</option>

<?php foreach ($listaUsuarios as $u): ?>

<option value="<?= $u['email'] ?>"
<?= (isset($_GET['usuario']) && $_GET['usuario'] == $u['email']) ? 'selected' : '' ?>>

<?= $u['email'] ?>

</option>

<?php endforeach; ?>

</select>

</form>

                        <?php if (!empty($dadosUsuariosFiltro)): ?>

                            <div class="card mt-4">

                                <div class="card-body">

                                    <h5 class="mb-3">Resultado do filtro de usuários</h5>

                                    <div class="table-responsive tabela-scroll">

                                        <table class="table table-bordered">

                                            <thead class="table-dark">

                                                <tr>

                                                    <th>Nome</th>
                                                    <th>Email</th>
                                                    <th>Data</th>
                                                    <th>Acessos</th>

                                                </tr>

                                            </thead>

                                            <tbody>

                                                <?php foreach ($dadosUsuariosFiltro as $d): ?>

                                                    <tr>

                                                        <td><?= $d['nome'] ?></td>
                                                        <td><?= $d['email'] ?></td>
                                                        <td><?= date('d/m/Y', strtotime($d['data_login'])) ?></td>
                                                        <td class="text-center"><?= $d['quantidade_login'] ?></td>

                                                    </tr>

                                                <?php endforeach; ?>

                                            </tbody>

                                        </table>

                                    </div>

                                </div>

                            </div>

                        <?php endif; ?>

                        <!-- TABELA FILTRO -->

                        <div class="table-responsive">

                            <table class="table table-bordered">

                                <thead class="table-dark">

                                    <tr>

                                        <th>Nome</th>
                                        <th>Email</th>
                                        <th>Data</th>
                                        <th>Acessos</th>

                                    </tr>

                                </thead>

                                <tbody>

                                    <?php foreach ($dadosMesFiltro as $d): ?>

                                        <tr>

                                            <td><?= $d['nome'] ?></td>
                                            <td><?= $d['email'] ?></td>
                                            <td><?= date('d/m/Y', strtotime($d['data_login'])) ?></td>
                                            <td class="text-center"><?= $d['quantidade_login'] ?></td>

                                        </tr>

                                    <?php endforeach; ?>

                                </tbody>

                            </table>

                        </div>

                    </div>

                </div>

            </div>

            <script>
                if (window.location.hash) {

                    document.querySelector(window.location.hash)
                        .scrollIntoView({
                            behavior: "smooth"
                        });

                }
            </script>

</body>

</html>