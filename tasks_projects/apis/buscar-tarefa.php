<?php
header("Content-Type: application/json");
require_once __DIR__ . '/../config/connection.php';

$id = (int) $_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM tarefa WHERE id = :id");
$stmt->execute([":id" => $id]);

echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));