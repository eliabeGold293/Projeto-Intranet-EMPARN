<?php
session_start();

// Se não estiver logado, manda para login
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['grau_acesso'])) {
    header("Location: ./public/login.php");
    exit;
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Portal EMPARN</title>
</head>
<body>
    <h1>Bem-vindo, <?= htmlspecialchars($_SESSION['usuario_nome']) ?>!</h1>
    <p>Você está logado com nível de acesso <?= $_SESSION['grau_acesso'] ?>.</p>
    <p><a href="./public/logout.php">Sair</a></p>
</body>
</html>
