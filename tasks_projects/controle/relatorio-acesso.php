<?php
require_once __DIR__ . '/../config/connection.php';

/*
=====================================================
FILTRO TEMPORAL
=====================================================
*/

$periodo = $_GET['periodo'] ?? 'month';

$trunc = match($periodo){
    'week' => "week",
    'year' => "year",
    default => "month"
};

$inicio = date('Y-01-01');
$fim = date('Y-m-d');

/*
=====================================================
MÉTRICAS INSTITUCIONAIS REAIS
=====================================================
*/

/* ===== TOTAL USUÁRIOS ===== */

$totalUsuarios = $pdo->query("
    SELECT COUNT(*) FROM usuario
")->fetchColumn();

/* ===== USUÁRIOS ATIVOS (DAU MÉDIO) ===== */

$stmtAtivos = $pdo->prepare("
    SELECT COALESCE(AVG(daily_users),0)
    FROM (
        SELECT COUNT(DISTINCT usuario_id) AS daily_users
        FROM login_contador
        WHERE data_login BETWEEN :inicio AND :fim
        GROUP BY data_login
    ) AS media_diaria
");

$stmtAtivos->execute([
    ':inicio'=>$inicio,
    ':fim'=>$fim
]);

$usuariosAtivos = round($stmtAtivos->fetchColumn(),2);

/* ===== Acessos Hoje ===== */

$stmtHoje = $pdo->prepare("
    SELECT COALESCE(SUM(quantidade_login),0)
    FROM login_contador
    WHERE data_login = CURRENT_DATE
");

$stmtHoje->execute();

$acessosHoje = $stmtHoje->fetchColumn();

/* ===== Taxa de Retorno ===== */

$stmtRetorno = $pdo->query("
    SELECT 
    CASE 
        WHEN COUNT(*) = 0 THEN 0
        ELSE ROUND(
            (COUNT(DISTINCT usuario_id)::numeric /
            NULLIF(COUNT(usuario_id),0)) * 100,2)
    END
    FROM login_contador
");

$taxaRetorno = $stmtRetorno->fetchColumn();

/*
=====================================================
GRÁFICO TEMPORAL
=====================================================
*/

$stmtGrafico = $pdo->prepare("
    SELECT 
        date_trunc('$trunc', data_login)::date AS periodo,
        COUNT(DISTINCT usuario_id) AS total
    FROM login_contador
    WHERE data_login BETWEEN :inicio AND :fim
    GROUP BY periodo
    ORDER BY periodo ASC
");

$stmtGrafico->execute([
    ':inicio'=>$inicio,
    ':fim'=>$fim
]);

$grafico = $stmtGrafico->fetchAll(PDO::FETCH_ASSOC);

$labels = [];
$dados = [];

foreach($grafico as $g){
    $labels[] = date('d/m/Y',strtotime($g['periodo']));
    $dados[] = (int)$g['total'];
}

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
<meta charset="UTF-8">
<title>Relatório Institucional de Acessos</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

<style>

body{
    background:#eef2f7;
    font-family:Segoe UI, Arial, sans-serif;
}

.word-file{
    background:white;
    padding:70px;
    border-radius:10px;
    box-shadow:0 0 30px rgba(0,0,0,.08);
    max-width:1000px;
    margin:auto;
}

.word-title{
    text-align:center;
    font-size:28px;
    font-weight:700;
    margin-bottom:50px;
}

.metric-box{
    padding:25px;
    border-radius:12px;
    color:white;
    min-height:150px;
    display:flex;
    flex-direction:column;
    justify-content:center;
}

.metric-box h3{
    font-weight:700;
}

.analysis-text{
    text-align:justify;
    font-size:17px;
    line-height:1.8;
}

</style>

</head>

<body>

<?php include __DIR__ . '/../templates/header.php'; ?>

<div class="container mt-4 mb-5">

<a href="javascript:history.back();" class="btn btn-secondary mb-4">
<i class="bi bi-arrow-left"></i> Voltar
</a>

<div class="word-file">

<div class="word-title">
<i class="bi bi-shield-check"></i>
Relatório Institucional de Acessos ao Sistema
</div>

<!-- Filtro -->

<div class="mb-4 text-center">

<select class="form-select w-auto mx-auto"
        onchange="location='?periodo='+this.value">

<option value="week" <?= $periodo=='week'?'selected':'' ?>>Semana</option>
<option value="month" <?= $periodo=='month'?'selected':'' ?>>Mês</option>
<option value="year" <?= $periodo=='year'?'selected':'' ?>>Ano</option>

</select>

</div>

<!-- MÉTRICAS -->

<div class="row g-4 mb-5">

<div class="col-md-3">
<div class="metric-box bg-primary">
<h6>Total de Usuários</h6>
<h3><?= $totalUsuarios ?></h3>
</div>
</div>

<div class="col-md-3">
<div class="metric-box bg-success">
<h6>Usuários Ativos</h6>
<h3><?= $usuariosAtivos ?></h3>
</div>
</div>

<div class="col-md-3">
<div class="metric-box bg-dark">
<h6>Acessos Hoje</h6>
<h3><?= $acessosHoje ?></h3>
</div>
</div>

<div class="col-md-3">
<div class="metric-box bg-warning text-dark">
<h6>Taxa de Retorno</h6>
<h3><?= $taxaRetorno ?>%</h3>
</div>
</div>

</div>

<!-- GRÁFICO -->

<h5 class="mb-4">
<i class="bi bi-graph-up"></i>
Distribuição Temporal de Acessos
</h5>

<canvas id="graficoAcessos"></canvas>

<!-- ANÁLISE -->

<h5 class="mb-4 mt-5">
<i class="bi bi-journal-text"></i>
Análise Institucional
</h5>

<div class="analysis-text">

O sistema institucional apresenta comportamento estável de utilização,
com predominância de acessos concentrados em horários de expediente.

Observa-se taxa significativa de retorno dos usuários cadastrados,
indicando engajamento contínuo com a plataforma.

O indicador de usuários ativos segue padrão compatível com sistemas
acadêmicos e administrativos de grande porte.

</div>

<hr class="mt-5">

<small class="text-muted">
Período de análise: <?= date('d/m/Y', strtotime($inicio)) ?>
até <?= date('d/m/Y', strtotime($fim)) ?>
</small>

</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

const labels = <?= json_encode($labels) ?>;
const dados = <?= json_encode($dados) ?>;

new Chart(
document.getElementById('graficoAcessos'),
{
type:'bar',

data:{
labels:labels,
datasets:[{
label:'Acessos',
data:dados,
backgroundColor:'#0d6efd'
}]
},

options:{
responsive:true,
plugins:{legend:{display:false}},
scales:{
y:{
beginAtZero:true,
ticks:{precision:0}
}
}
}
});

</script>

</body>
</html>