<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/../utils/log-action.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$id        = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$nome      = $_POST['nome'] ?? null;
$email     = $_POST['email'] ?? null;
$classe_id = isset($_POST['classe_id']) ? (int) $_POST['classe_id'] : null;
$area_id   = isset($_POST['area_id']) ? (int) $_POST['area_id'] : null;
$mudarSenha = $_POST['password'] ?? '';

if ($id <= 0) {
    echo "Informe um ID válido.";
    exit;
}

try {

    // ================================
    // BUSCAR DADOS ANTIGOS
    // ================================
    $stmtAntigo = $pdo->prepare("SELECT nome, email FROM usuario WHERE id = :id");
    $stmtAntigo->execute([':id' => $id]);
    $usuarioAntigo = $stmtAntigo->fetch(PDO::FETCH_ASSOC);

    if (!$usuarioAntigo) {
        echo "Usuário não encontrado.";
        exit;
    }

    $campos = [];
    $params = [':id' => $id];
    $alteracoes = [];

    // ================================
    // MONTAR ALTERAÇÕES
    // ================================
    if ($nome) {
        $campos[] = "nome = :nome";
        $params[':nome'] = $nome;
        $alteracoes[] = "nome: {$usuarioAntigo['nome']} → {$nome}";
    }

    if ($email) {

        // ================================
        // VERIFICAR EMAIL DUPLICADO (OUTRO USUÁRIO)
        // ================================
        $stmtCheck = $pdo->prepare("
        SELECT id FROM usuario 
        WHERE email = :email AND id != :id
        LIMIT 1
        ");

        $stmtCheck->execute([
            ':email' => $email,
            ':id' => $id
        ]);

        if ($stmtCheck->fetch()) {
            echo "Este e-mail já está em uso por outro usuário.";
            exit;
        }

        $campos[] = "email = :email";
        $params[':email'] = $email;
        $alteracoes[] = "email: {$usuarioAntigo['email']} → {$email}";
    }

    if ($classe_id) {
        $campos[] = "classe_id = :classe_id";
        $params[':classe_id'] = $classe_id;
        $alteracoes[] = "classe_id alterado";
    }

    if ($area_id) {
        $campos[] = "area_id = :area_id";
        $params[':area_id'] = $area_id;
        $alteracoes[] = "area_id alterado";
    }

    // ================================
    // SENHA
    // ================================
    $senhaTemporaria = null;

    if ($mudarSenha === 'sim') {

        $senhaTemporaria = bin2hex(random_bytes(4));
        $senhaHash = password_hash($senhaTemporaria, PASSWORD_DEFAULT);

        $campos[] = "senha = :senha";
        $campos[] = "primeiro_acesso = true";

        $params[':senha'] = $senhaHash;

        $alteracoes[] = "senha redefinida";
    }

    // ================================
    // EXECUTAR UPDATE
    // ================================
    if (!empty($campos)) {

        $sql = "UPDATE usuario SET " . implode(", ", $campos) . " WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        // ================================
        // LOG DE AÇÃO 
        // ================================
        try {

            $usuarioLogadoId = $_SESSION['usuario_id'] ?? null;

            $descricao = "Atualizou usuário ID {$id}: " . implode(', ', $alteracoes);

            registrarLog(
                $pdo,
                $usuarioLogadoId,
                'usuario',
                'UPDATE',
                $descricao
            );
        } catch (Exception $e) {
            error_log("Erro ao registrar log: " . $e->getMessage());
        }

        // ================================
        // EMAIL (SE SENHA ALTERADA)
        // ================================
        if ($mudarSenha === 'sim' && $senhaTemporaria) {

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
                $mail->Subject = 'Senha redefinida - Sistema Emparn';
                $mail->Body = "
                    Olá {$usuarioAntigo['nome']},<br><br>
                    <b>Sua senha foi redefinida pelo administrador.</b><br><br>
                    <b>Nova senha temporária:</b> {$senhaTemporaria}<br>
                    <b>Email:</b> {$usuarioAntigo['email']}<br><br>
                    No próximo login será solicitado que você altere sua senha.
                ";

                $mail->send();
            } catch (Exception $e) {
                error_log("Erro ao enviar email: {$e->getMessage()}");
            }
        }

        echo "Alterações realizadas com sucesso!";
    } else {
        echo "Nenhum campo foi informado para atualização.";
    }
} catch (PDOException $e) {

    // Violação de UNIQUE (email duplicado)
    if ($e->getCode() === '23505') {
        echo "Este e-mail já está em uso por outro usuário.";
        exit;
    }

    echo "Erro ao salvar alterações: " . $e->getMessage();
}
