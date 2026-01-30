<?php
session_start();
require_once __DIR__ . '/../config/connection.php';

$email = $_POST['email'] ?? '';
$senha = $_POST['senha'] ?? '';

$sql = "SELECT id, senha, primeiro_acesso FROM usuario WHERE email = :email LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':email', $email);
$stmt->execute();

$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (
    $usuario &&
    (int)$usuario['primeiro_acesso'] === 1 &&
    password_verify($senha, $usuario['senha'])
) {

    $_SESSION['primeiro_acesso'] = true;
    $_SESSION['usuario_id'] = $usuario['id'];

    header('Location: primeiro-acesso');
    exit;

} elseif ($usuario && password_verify($senha, $usuario['senha'])) {

    header('Location: home');
    exit;

} else {
    echo 'Credenciais inválidas';
}
