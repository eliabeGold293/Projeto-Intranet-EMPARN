<?php
require_once __DIR__ . '/../config/connection.php';

$data = json_decode(file_get_contents("php://input"), true);

$projeto = $data['projeto_id'];
$usuario = $data['usuario_id'];

$stmt = $pdo->prepare("
    DELETE FROM projeto_usuario
    WHERE projeto_id = ?
    AND usuario_id = ?
");

$stmt->execute([$projeto, $usuario]);

echo json_encode([
    "status" => "success"
]);
