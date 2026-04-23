<?php

declare(strict_types=1);

ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

header('Content-Type: application/json; charset=UTF-8');

session_start();

require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/../utils/log-action.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

function response(bool $success, string $message, array $extra = [], int $status = 200): void
{
    http_response_code($status);
    echo json_encode(array_merge([
        "success" => $success,
        "message" => $message
    ], $extra), JSON_UNESCAPED_UNICODE);
    exit;
}

try {

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        response(false, "Método não permitido", [], 405);
    }

    $nome      = trim($_POST['nome'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $classe_id = isset($_POST['classe_id']) ? (int) $_POST['classe_id'] : 0;
    $area_id   = isset($_POST['area_id'])   ? (int) $_POST['area_id']   : 0;
    $senhaRecebida = $_POST['senha'] ?? null;

    if ($nome === '' || $email === '' || $classe_id <= 0 || $area_id <= 0) {
        response(false, "Preencha todos os campos obrigatórios.", [], 422);
    }

    // ================================
    // VERIFICAR EMAIL DUPLICADO
    // ================================
    $stmt = $pdo->prepare("SELECT id FROM usuario WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => $email]);

    if ($stmt->fetch()) {
        response(false, "Este e-mail já está cadastrado.", [], 409);
    }

    // ================================
    // SENHA
    // ================================
    $senhaTemporaria = $senhaRecebida ?: bin2hex(random_bytes(4));
    $senhaHash = password_hash($senhaTemporaria, PASSWORD_DEFAULT);
    $primeiroAcesso = true;

    // ================================
    // INSERT
    // ================================
    $stmt = $pdo->prepare("
        INSERT INTO usuario 
        (nome, email, senha, classe_id, area_id, primeiro_acesso)
        VALUES (:nome, :email, :senha, :classe_id, :area_id, :primeiro_acesso)
        RETURNING id
    ");

    $stmt->execute([
        ':nome'            => $nome,
        ':email'           => $email,
        ':senha'           => $senhaHash,
        ':classe_id'       => $classe_id,
        ':area_id'         => $area_id,
        ':primeiro_acesso' => $primeiroAcesso
    ]);

    $novoUsuarioId = $stmt->fetchColumn();

    // ================================
    // LOG DE AÇÃO
    // ================================
    try {

        $usuarioLogadoId = $_SESSION['usuario_id'] ?? null;

        registrarLog(
            $pdo,
            $usuarioLogadoId,
            'usuario',
            'CREATE',
            "Criou o usuário ID {$novoUsuarioId} ({$nome})"
        );

    } catch (Exception $e) {
        error_log("Erro ao registrar log: " . $e->getMessage());
    }

    // ================================
    // EMAIL
    // ================================
    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = $_ENV['MAIL_HOST'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['MAIL_USERNAME'];
        $mail->Password   = $_ENV['MAIL_PASSWORD'];
        $mail->Port       = $_ENV['MAIL_PORT'];
        $mail->SMTPSecure = $_ENV['MAIL_ENCRYPTION'] === 'tls'
            ? PHPMailer::ENCRYPTION_STARTTLS
            : PHPMailer::ENCRYPTION_SMTPS;

        $mail->setFrom($_ENV['MAIL_FROM'], $_ENV['MAIL_NAME']);
        $mail->addAddress($email, $nome);
        $mail->isHTML(true);
        $mail->Subject = 'Acesso ao Sistema Emparn';
        $mail->Body = "
            Olá {$nome},<br><br>
            <b>Sua conta foi criada no sistema da EMPARN.</b><br><br>
            <b>Email:</b> {$email}<br>
            <b>Senha:</b> {$senhaTemporaria}<br>
        ";

        $mail->send();
    } catch (Exception $e) {
        error_log("Erro ao enviar email: {$e->getMessage()}");
    }

    // ================================
    // RESPOSTA
    // ================================
    response(true, "Usuário criado com sucesso!", [
        "user_id" => $novoUsuarioId
    ]);

} catch (PDOException $e) {

    // PostgreSQL: violação de UNIQUE
    if ($e->getCode() === '23505') {
        response(false, "Este e-mail já está cadastrado.", [], 409);
    }

    response(false, "Erro no banco de dados.", [
        "error" => $e->getMessage()
    ], 500);
} catch (Throwable $e) {

    response(false, "Erro inesperado.", [
        "error" => $e->getMessage()
    ], 500);
}