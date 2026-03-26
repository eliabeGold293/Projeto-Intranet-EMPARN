<?php
require_once __DIR__ . '/../config/connection.php';

$tipo = $_GET['tipo'] ?? 'acao'; // acao ou login
$filtro = $_GET['filtro'] ?? 'TODOS';

if ($tipo === 'acao') {

    $sql = "
        SELECT l.descricao, l.acao, l.data_acao, u.nome
        FROM log_acao l
        LEFT JOIN usuario u ON u.id = l.usuario_id
        WHERE l.acao IN ('CREATE','UPDATE','DELETE')
    ";

    if ($filtro !== 'TODOS') {
        $sql .= " AND l.acao = :filtro";
    }

    $sql .= " ORDER BY l.data_acao DESC LIMIT 100";

} else {

    $sql = "
        SELECT l.descricao, l.acao, l.data_acao, u.nome
        FROM log_acao l
        LEFT JOIN usuario u ON u.id = l.usuario_id
        WHERE l.acao IN ('LOGIN','LOGIN_PRIMEIRO_ACESSO')
    ";

    if ($filtro !== 'TODOS') {
        $sql .= " AND l.acao = :filtro";
    }

    $sql .= " ORDER BY l.data_acao DESC LIMIT 50";
}

$stmt = $pdo->prepare($sql);

if ($filtro !== 'TODOS') {
    $stmt->bindParam(':filtro', $filtro);
}

$stmt->execute();
$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($resultados);