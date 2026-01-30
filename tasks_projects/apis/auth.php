<?php
session_start();
require_once __DIR__ . '/../config/connection.php';

$email = $_POST['email'] ?? '';
$senha = $_POST['senha'] ?? '';

// Buscar usuário + grau_acesso da classe
$sql = "
SELECT u.id, u.nome, u.senha, u.primeiro_acesso, c.grau_acesso
FROM usuario u
LEFT JOIN classe_usuario c ON u.classe_id = c.id
WHERE u.email = :email
LIMIT 1
";

$stmt = $pdo->prepare($sql);
$stmt->bindParam(':email', $email);
$stmt->execute();

$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if ($usuario && password_verify($senha, $usuario['senha'])) {

    // Cria a sessão básica
    $_SESSION['usuario_id'] = $usuario['id'];
    $_SESSION['grau_acesso'] = $usuario['grau_acesso'] ?? null;
    $_SESSION['usuario_nome'] = $usuario['nome'] ?? null;

    // Primeiro acesso?
    if ((int)$usuario['primeiro_acesso'] === 1) {
        $_SESSION['primeiro_acesso'] = true;
        header('Location: primeiro-acesso');
    } else {
        // Limpar flag de primeiro acesso se existir
        unset($_SESSION['primeiro_acesso']);
        header('Location: home');
    }

    exit;

} else {
    echo 'Credenciais inválidas';
}
