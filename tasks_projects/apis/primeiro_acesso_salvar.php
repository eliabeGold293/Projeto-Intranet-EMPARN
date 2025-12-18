<?php
session_start();
require_once __DIR__ . '/../config/connection.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método inválido.']);
    exit;
}

if (empty($_SESSION['usuario_id']) || empty($_SESSION['primeiro_acesso'])) {
    echo json_encode(['success' => false, 'message' => 'Sessão inválida.']);
    exit;
}

$senha1 = trim($_POST['senha1'] ?? '');
$senha2 = trim($_POST['senha2'] ?? '');

if ($senha1 === '' || $senha2 === '') {
    echo json_encode(['success' => false, 'message' => 'Preencha todos os campos.']);
    exit;
}

if ($senha1 !== $senha2) {
    echo json_encode(['success' => false, 'message' => 'As senhas não coincidem.']);
    exit;
}

// Hash seguro
$senhaHash = password_hash($senha1, PASSWORD_DEFAULT);

// Atualiza senha e marca primeiro acesso como finalizado
$stmt = $pdo->prepare("
    UPDATE usuario
    SET senha = :senha,
        primeiro_acesso = FALSE
    WHERE id = :id
");

$stmt->execute([
    ':senha' => $senhaHash,
    ':id' => $_SESSION['usuario_id']
]);

// Log
$pdo->prepare("
    INSERT INTO log_acao (usuario_id, entidade, acao, descricao)
    VALUES (:id, 'auth', 'PRIMEIRO_ACESSO', 'Senha criada no primeiro acesso')
")->execute([
    ':id' => $_SESSION['usuario_id']
]);

// Limpa flag da sessão
unset($_SESSION['primeiro_acesso']);

echo json_encode([
    'success' => true,
    'message' => 'Senha criada com sucesso! Redirecionando...'
]);
