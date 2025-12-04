<?php
session_start();
require_once "../config/connection.php";

header("Content-Type: application/json");

function response($success, $message, $extra = []) {
    echo json_encode(array_merge([
        "success" => $success,
        "message" => $message
    ], $extra));
    exit;
}

try {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        response(false, "Método inválido.");
    }

    $email  = trim($_POST['email'] ?? "");
    $senha1 = trim($_POST['senha1'] ?? "");
    $senha2 = trim($_POST['senha2'] ?? "");

    if (!$email || !$senha1 || !$senha2) {
        response(false, "Preencha todos os campos.");
    }

    if ($senha1 !== $senha2) {
        response(false, "As senhas não coincidem.");
    }

    if (strlen($senha1) < 6) {
        response(false, "A senha deve ter no mínimo 6 caracteres.");
    }

    // Buscar usuário
    $stmt = $pdo->prepare("SELECT id, primeiro_acesso FROM usuario WHERE email = :email LIMIT 1");
    $stmt->execute([":email" => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        response(false, "Usuário não encontrado.");
    }

    if (!$user['primeiro_acesso']) {
        response(false, "Este usuário já realizou o primeiro acesso.");
    }

    // Atualizar senha
    $novaSenhaHash = password_hash($senha1, PASSWORD_DEFAULT);

    $update = $pdo->prepare("
        UPDATE usuario
        SET senha = :senha, primeiro_acesso = FALSE, data_modificacao = NOW()
        WHERE email = :email
    ");
    $update->execute([
        ":senha" => $novaSenhaHash,
        ":email" => $email
    ]);

    // Registrar LOG
    $stmtLog = $pdo->prepare("
        INSERT INTO log_acao (usuario_id, entidade, acao, descricao)
        VALUES (:usuario_id, 'auth', 'PRIMEIRO_ACESSO', 'Senha definitiva criada.')
    ");
    $stmtLog->execute([":usuario_id" => $user['id']]);

    response(true, "Senha criada com sucesso! Redirecionando...");

} catch (Exception $e) {
    response(false, "Erro ao processar.", ["error" => $e->getMessage()]);
}
