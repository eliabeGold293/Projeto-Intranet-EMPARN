<?php
require_once "../config/connection.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

header("Content-Type: application/json; charset=UTF-8");

function response($success, $message, $extra = [])
{
    echo json_encode(array_merge([
        "success" => $success,
        "message" => $message
    ], $extra));
    exit;
}

try {

    // ================================
    // 1. RECEBER DADOS
    // ================================
    $nome      = $_POST['nome']      ?? null;
    $email     = $_POST['email']     ?? null;
    $classe_id = isset($_POST['classe_id']) ? (int) $_POST['classe_id'] : null;
    $area_id   = isset($_POST['area_id'])   ? (int) $_POST['area_id']   : null;

    // NOVO: senha recebida do front-end (pode ser vazia)
    $senhaRecebida = $_POST['senha'] ?? null;

    if (!$nome || !$email || !$classe_id || !$area_id) {
        response(false, "Preencha todos os campos obrigatórios.");
    }

    // ================================
    // 2. DEFINIR SENHA (RECEBIDA OU GERADA)
    // ================================
    if (!empty($senhaRecebida)) {
        // Se senha veio do formulário, usa ela
        $senhaTemporaria = $senhaRecebida;
    } else {
        // Caso contrário, gera uma aleatória
        $senhaTemporaria = bin2hex(random_bytes(4));
    }

    // O que será salvo no banco é sempre o hash:
    $senhaHash = password_hash($senhaTemporaria, PASSWORD_DEFAULT);
    $primeiroAcesso = true;

    // ================================
    // 3. INSERIR NO BANCO
    // ================================
    $sql = "INSERT INTO usuario 
        (nome, email, senha, classe_id, area_id, primeiro_acesso)
        VALUES (:nome, :email, :senha, :classe_id, :area_id, :primeiro_acesso)
        RETURNING id";

    $stmt = $pdo->prepare($sql);
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
    // 4. ENVIAR EMAIL
    // ================================
    $assunto = "Acesso ao Sistema - Sua Senha";
    $mensagem = "
        Olá {$nome},<br><br>
        Sua conta foi criada no sistema.<br><br>
        Aqui está sua senha:<br><br>
        <b>Senha: {$senhaTemporaria}</b><br><br>
        - Equipe do Sistema
    ";

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'eliabeflorencio@gmail.com';      // ALTERAR
        $mail->Password   = 'ucny vcng qfqs uhww';       // ALTERAR
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('eliabeflorencio@gmail.com', 'Sistema');
        $mail->addAddress($email, $nome);

        $mail->isHTML(true);
        $mail->Subject = $assunto;
        $mail->Body    = $mensagem;

        $mail->send();

    } catch (Exception $e) {
        error_log("Erro ao enviar email: {$mail->ErrorInfo}");
    }

    // ================================
    // 5. RETORNO FINAL
    // ================================
    response(true, "Usuário criado e e-mail enviado!", [
        "user_id" => $novoUsuarioId
    ]);

} catch (PDOException $e) {

    response(false, "Erro no banco de dados.", [
        "error" => $e->getMessage(),
        "trace" => $e->getTraceAsString()
    ]);

} catch (Exception $e) {

    response(false, "Erro inesperado.", [
        "error" => $e->getMessage()
    ]);

}
