<?php
session_start();
$nome_usuario = $_SESSION['usuario_nome'] ?? 'Não identificado';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;


// Pegando parâmetros
$inicio = $_GET['inicio'] ?? date('Y-01-01');
$fim    = $_GET['fim'] ?? date('Y-12-31');

// Consulta
$sql = "
SELECT u.id, u.nome, SUM(l.quantidade_login) as total_logins
FROM login_contador l
JOIN usuario u ON u.id = l.usuario_id
WHERE l.data_login BETWEEN :inicio AND :fim
GROUP BY u.id, u.nome
ORDER BY total_logins DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute(['inicio' => $inicio, 'fim' => $fim]);
$usuarios = $stmt->fetchAll();

// Dados auxiliares
$top5 = array_slice($usuarios, 0, 5);
$bottom5 = array_slice(array_reverse($usuarios), 0, 5);
$maior = $usuarios[0] ?? null;
$menor = !empty($usuarios) ? $usuarios[count($usuarios)-1] : null;

$sql_total = "SELECT COUNT(DISTINCT usuario_id) as total 
FROM login_contador 
WHERE data_login BETWEEN :inicio AND :fim";

$stmt = $pdo->prepare($sql_total);
$stmt->execute(['inicio' => $inicio, 'fim' => $fim]);
$total_usuarios = $stmt->fetch()['total'] ?? 0;
$total_acessos = array_sum(array_column($usuarios, 'total_logins'));
$media_acessos = $total_usuarios > 0 ? round($total_acessos / $total_usuarios, 2) : 0;

// HTML do PDF (estilo ABNT simples)
$html = "
<h2 style='text-align:center;'>RELATÓRIO DE ACESSOS AO SISTEMA</h2>

<p><strong>Instituição:</strong>Empresa de Pesquisa Agropecuária do Rio Grande do Norte - EMPARN</p>
<p><strong>Emitido por:</strong> {$nome_usuario}</p>
<p><strong>Período:</strong> $inicio até $fim</p>
<p><strong>Data de emissão:</strong> " . date('d/m/Y') . "</p>

<hr>

<h3>1. Total de usuários ativos no período</h3>

<table border='1' width='50%' cellpadding='5'>
<tr>
    <th>Total de usuários</th>
</tr>
<tr>
    <td>{$total_usuarios}</td>
</tr>
</table>

<h3>2. Usuários e quantidade de acessos</h3>

<table border='1' width='100%' cellspacing='0' cellpadding='5'>
<tr>
    <th>ID</th>
    <th>Nome</th>
    <th>Acessos</th>
</tr>";

foreach ($usuarios as $u) {
    $html .= "
    <tr>
        <td>{$u['id']}</td>
        <td>{$u['nome']}</td>
        <td>{$u['total_logins']}</td>
    </tr>";
}

$html .= "</table>";

$html .= "
<h3>3. Média de acessos por usuário no período</h3>

<table border='1' width='50%' cellpadding='5'>
<tr>
    <th>Média de acessos</th>
</tr>
<tr>
    <td>{$media_acessos} acessos</td>
</tr>
</table>
";

$html .= "<h3>4. Top 5 usuários que mais acessaram</h3>
<table border='1' width='100%' cellpadding='5'>
<tr><th>ID</th><th>Nome</th><th>Acessos</th></tr>";

foreach ($top5 as $u) {
    $html .= "<tr>
        <td>{$u['id']}</td>
        <td>{$u['nome']}</td>
        <td>{$u['total_logins']}</td>
    </tr>";
}

$html .= "</table>";

$html .= "<h3>5. Top 5 usuários que menos acessaram</h3>
<table border='1' width='100%' cellpadding='5'>
<tr><th>ID</th><th>Nome</th><th>Acessos</th></tr>";

foreach ($bottom5 as $u) {
    $html .= "<tr>
        <td>{$u['id']}</td>
        <td>{$u['nome']}</td>
        <td>{$u['total_logins']}</td>
    </tr>";
}

$html .= "</table>";

if ($maior && $menor) {
    $html .= "
    <h3>6. Destaques</h3>
    <p><strong>Usuário com mais acessos:</strong> {$maior['nome']} ({$maior['total_logins']})</p>
    <p><strong>Usuário com menos acessos:</strong> {$menor['nome']} ({$menor['total_logins']})</p>
    ";
}

// Gerar PDF
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Exibir no navegador
$dompdf->stream("relatorio_acessos.pdf", ["Attachment" => false]);