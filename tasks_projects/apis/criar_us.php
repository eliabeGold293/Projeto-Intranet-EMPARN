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
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'eliabeflorencio@gmail.com';
        $mail->Password   = 'ucny vcng qfqs uhww';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('SEU_EMAIL@gmail.com', 'Sistema');
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

    response(false, "Erro no banco de dados.", [
        "error" => $e->getMessage()
    ], 500);

} catch (Throwable $e) {

    response(false, "Erro inesperado.", [
        "error" => $e->getMessage()
    ], 500);
}