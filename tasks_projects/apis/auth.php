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

    // ======= CONTABILIZAR LOGIN DIÁRIO (UPSERT - PRO SOLUTION) =======

    $timezone = new DateTimeZone('America/Sao_Paulo');
    $dataAtual = new DateTime('now', $timezone);
    $hoje = $dataAtual->format('Y-m-d');

    $usuarioId = $usuario['id'];

    $sqlLog = "
    INSERT INTO login_contador (usuario_id, data_login, quantidade_login)
    VALUES (:usuario_id, :data_login, 1)

    ON CONFLICT (usuario_id, data_login)
    DO UPDATE SET
        quantidade_login = login_contador.quantidade_login + 1
    ";

    $stmtLog = $pdo->prepare($sqlLog);

    $stmtLog->execute([
        ':usuario_id' => $usuarioId,
        ':data_login' => $hoje
    ]);

    // ======= Primeiro acesso? =======
    if ((int)$usuario['primeiro_acesso'] === 1) {
        $_SESSION['primeiro_acesso'] = true;
        header('Location: primeiro-acesso');
    } else {
        unset($_SESSION['primeiro_acesso']);
        header('Location: home');
    }

    exit;

} else {
    echo 'Credenciais inválidas';
}