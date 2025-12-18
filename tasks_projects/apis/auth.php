<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/../config/connection.php';

// aceita somente POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['erro_login'] = 'Método inválido.';
    header('Location: index.php?url=login');
    exit;
}

$email = trim($_POST['email'] ?? '');
$senha = trim($_POST['senha'] ?? '');

if ($email === '' || $senha === '') {
    $_SESSION['erro_login'] = 'Preencha todos os campos.';
    header('Location: index.php?url=login');
    exit;
}

// busca usuário
$sql = "
    SELECT 
        u.id,
        u.nome,
        u.email,
        u.senha,
        u.primeiro_acesso,
        c.nome AS classe_nome,
        c.grau_acesso
    FROM usuario u
    JOIN classe_usuario c ON c.id = u.classe_id
    WHERE u.email = :email
    LIMIT 1
";

$stmt = $pdo->prepare($sql);
$stmt->execute([':email' => $email]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

// usuário não existe
if (!$usuario) {
    $pdo->prepare("
        INSERT INTO log_acao (usuario_id, entidade, acao, descricao)
        VALUES (NULL, 'auth', 'FALHA_LOGIN', :descricao)
    ")->execute([
        ':descricao' => "Email inexistente: {$email}"
    ]);

    $_SESSION['erro_login'] = 'Email ou senha inválidos.';
    header('Location: index.php?url=login');
    exit;
}

// senha incorreta
if (!password_verify($senha, $usuario['senha'])) {
    $pdo->prepare("
        INSERT INTO log_acao (usuario_id, entidade, acao, descricao)
        VALUES (:usuario_id, 'auth', 'FALHA_LOGIN', :descricao)
    ")->execute([
        ':usuario_id' => $usuario['id'],
        ':descricao' => 'Senha incorreta'
    ]);

    $_SESSION['erro_login'] = 'Email ou senha inválidos.';
    header('Location: index.php?url=login');
    exit;
}

// LOGIN OK
$_SESSION['usuario_id']      = $usuario['id'];
$_SESSION['usuario_nome']    = $usuario['nome'];
$_SESSION['usuario_email']   = $usuario['email'];
$_SESSION['classe_nome']     = $usuario['classe_nome'];
$_SESSION['grau_acesso']     = $usuario['grau_acesso'];
$_SESSION['primeiro_acesso'] = (bool) $usuario['primeiro_acesso'];

// log de sucesso
$pdo->prepare("
    INSERT INTO log_acao (usuario_id, entidade, acao, descricao)
    VALUES (:usuario_id, 'auth', 'LOGIN', 'Login realizado com sucesso')
")->execute([
    ':usuario_id' => $usuario['id']
]);

// redirecionamento inteligente
if ($usuario['primeiro_acesso']) {
    header('Location: index.php?url=primeiro-acesso');
} else {
    header('Location: index.php?url=home');
}
exit;
