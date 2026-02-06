<?php
require_once __DIR__ . '/../config/connection.php';

$projeto = $_GET['id'] ?? 0;

$stmt = $pdo->query("
    SELECT 
        u.id,
        u.nome,
        c.nome AS classe,
        a.nome AS area
    FROM usuario u
    LEFT JOIN classe_usuario c ON c.id = u.classe_id
    LEFT JOIN area_atuacao a   ON a.id = u.area_id
    ORDER BY u.nome
");

$usuariosSistema = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("
    SELECT u.id, u.nome, pu.papel_id
    FROM projeto_usuario pu
    JOIN usuario u ON u.id = pu.usuario_id
    WHERE pu.projeto_id = ?
");
$stmt->execute([$projeto]);
$usuariosProjeto = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    "usuariosSistema" => $usuariosSistema,
    "usuariosProjeto" => $usuariosProjeto
]);
