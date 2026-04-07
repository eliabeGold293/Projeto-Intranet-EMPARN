<?php
require_once __DIR__ . '/../config/connection.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

/* ======================
FILTROS
====================== */

$tipoPeriodo = $_GET['tipo'] ?? 'mes'; // dia | mes | ano
$data = $_GET['data'] ?? '';
$mes  = $_GET['mes'] ?? date('m');
$ano  = $_GET['ano'] ?? date('Y');

$usuario = $_GET['usuario'] ?? '';
$classe  = $_GET['classe'] ?? '';

$where = [];
$params = [];

/* ======================
FILTROS BASE
====================== */

if ($usuario) {
    $where[] = "u.id = :usuario";
    $params[':usuario'] = $usuario;
}

if ($classe) {
    $where[] = "u.classe_id = :classe";
    $params[':classe'] = $classe;
}

/* ======================
PERÍODO
====================== */

$groupBy = "DATE(lc.data_login)";
$titulo = "";

switch ($tipoPeriodo) {

    case 'dia':
        if ($data) {
            $where[] = "DATE(lc.data_login) = :data";
            $params[':data'] = $data;
            $titulo = "Dia " . date('d/m/Y', strtotime($data));
        } else {
            $where[] = "DATE(lc.data_login) = CURRENT_DATE";
            $titulo = "Hoje";
        }
        break;

    case 'ano':
        $where[] = "EXTRACT(YEAR FROM lc.data_login) = :ano";
        $params[':ano'] = $ano;
        $groupBy = "TO_CHAR(lc.data_login, 'MM')";
        $titulo = "Ano $ano";
        break;

    default: // mês
        $where[] = "EXTRACT(MONTH FROM lc.data_login) = :mes";
        $where[] = "EXTRACT(YEAR FROM lc.data_login) = :ano";
        $params[':mes'] = $mes;
        $params[':ano'] = $ano;
        $titulo = "Mês $mes/$ano";
        break;
}

$whereSQL = "WHERE " . implode(" AND ", $where);

/* ======================
FUNÇÃO
====================== */

function execQuery($pdo, $sql, $params = [])
{
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

/* ======================
CARDS
====================== */

$total = execQuery($pdo, "
SELECT COALESCE(SUM(lc.quantidade_login),0)
FROM login_contador lc
JOIN usuario u ON u.id = lc.usuario_id
$whereSQL
", $params)->fetchColumn();

$ativos = execQuery($pdo, "
SELECT COUNT(DISTINCT lc.usuario_id)
FROM login_contador lc
JOIN usuario u ON u.id = lc.usuario_id
$whereSQL
", $params)->fetchColumn();

/* 🔥 CORREÇÃO PROFISSIONAL AQUI */
$paramsUsuarios = [];

$sqlUsuarios = "SELECT COUNT(*) FROM usuario";

if ($classe) {
    $sqlUsuarios .= " WHERE classe_id = :classe";
    $paramsUsuarios[':classe'] = $classe;
}

$totalUsuarios = execQuery($pdo, $sqlUsuarios, $paramsUsuarios)->fetchColumn();

$inativos = $totalUsuarios - $ativos;

/* ======================
GRÁFICO
====================== */

$sqlGrafico = "
SELECT $groupBy as periodo, SUM(lc.quantidade_login) total
FROM login_contador lc
JOIN usuario u ON u.id = lc.usuario_id
$whereSQL
GROUP BY periodo
ORDER BY periodo
";

$grafico = execQuery($pdo, $sqlGrafico, $params)->fetchAll(PDO::FETCH_ASSOC);

$labels = [];
$dados = [];

foreach ($grafico as $g) {
    $labels[] = $g['periodo'];
    $dados[] = (int)$g['total'];
}

if (!$labels) {
    $labels = ['Sem dados'];
    $dados = [0];
}

/* ======================
RANKING
====================== */

$ranking = execQuery($pdo, "
SELECT u.nome, SUM(lc.quantidade_login) total
FROM login_contador lc
JOIN usuario u ON u.id = lc.usuario_id
$whereSQL
GROUP BY u.nome
ORDER BY total DESC
LIMIT 10
", $params)->fetchAll(PDO::FETCH_ASSOC);

/* ======================
LISTAS
====================== */

$usuarios = $pdo->query("SELECT id,nome FROM usuario ORDER BY nome")->fetchAll();
$classes  = $pdo->query("SELECT id,nome FROM classe_usuario ORDER BY nome")->fetchAll();

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Dashboard PRO</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body { background: #f4f6fa; }

        .card-pro {
            border-radius: 16px;
            padding: 20px;
            background: white;
            box-shadow: 0 8px 20px rgba(0,0,0,0.05);
        }

        .metric {
            font-size: 28px;
            font-weight: bold;
        }

        .icon-box {
            font-size: 26px;
            background: #eef2ff;
            padding: 10px;
            border-radius: 10px;
            color: #4f46e5;
        }
    </style>
</head>

<body>

<div class="container mt-4">

    <h3 class="mb-2">
        <i class="bi bi-speedometer2"></i> Dashboard PRO
    </h3>
    <div class="text-muted mb-4"><?= $titulo ?></div>

    <!-- FILTROS -->
    <form class="row g-2 mb-4">

        <div class="col-md-2">
            <select name="tipo" class="form-select">
                <option value="dia" <?= $tipoPeriodo=='dia'?'selected':'' ?>>Dia</option>
                <option value="mes" <?= $tipoPeriodo=='mes'?'selected':'' ?>>Mês</option>
                <option value="ano" <?= $tipoPeriodo=='ano'?'selected':'' ?>>Ano</option>
            </select>
        </div>

        <div class="col-md-2">
            <input type="date" name="data" value="<?= $data ?>" class="form-control">
        </div>

        <div class="col-md-2">
            <input type="number" name="mes" value="<?= $mes ?>" min="1" max="12" class="form-control" placeholder="Mês">
        </div>

        <div class="col-md-2">
            <input type="number" name="ano" value="<?= $ano ?>" class="form-control" placeholder="Ano">
        </div>

        <div class="col-md-2">
            <button class="btn btn-primary w-100">
                <i class="bi bi-funnel"></i> Filtrar
            </button>
        </div>

    </form>

    <!-- CARDS -->
    <div class="row mb-4">

        <div class="col-md-4">
            <div class="card-pro d-flex justify-content-between">
                <div>
                    <div>Total</div>
                    <div class="metric"><?= $total ?></div>
                </div>
                <i class="bi bi-bar-chart icon-box"></i>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card-pro d-flex justify-content-between">
                <div>
                    <div>Ativos</div>
                    <div class="metric"><?= $ativos ?></div>
                </div>
                <i class="bi bi-people icon-box"></i>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card-pro d-flex justify-content-between">
                <div>
                    <div>Inativos</div>
                    <div class="metric"><?= $inativos ?></div>
                </div>
                <i class="bi bi-person-x icon-box"></i>
            </div>
        </div>

    </div>

    <!-- GRÁFICO -->
    <div class="card-pro mb-4">
        <h5><i class="bi bi-graph-up"></i> Evolução</h5>
        <canvas id="grafico"></canvas>
    </div>

    <!-- RANKING -->
    <div class="card-pro">
        <h5><i class="bi bi-trophy"></i> Ranking</h5>

        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Usuário</th>
                    <th>Acessos</th>
                </tr>
            </thead>
            <tbody>
                <?php $i=1; foreach ($ranking as $r): ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td><?= $r['nome'] ?></td>
                    <td><?= $r['total'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>

<script>
new Chart(document.getElementById('grafico'), {
    type: 'line',
    data: {
        labels: <?= json_encode($labels) ?>,
        datasets: [{
            label: 'Acessos',
            data: <?= json_encode($dados) ?>,
            tension: 0.4,
            fill: true
        }]
    }
});
</script>

</body>
</html>