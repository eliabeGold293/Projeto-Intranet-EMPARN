<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/connection.php';
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

    // Buscar usuário atual
    $stmtAntigo = $pdo->prepare("SELECT nome, email FROM usuario WHERE id = :id");
    $stmtAntigo->execute([':id' => $id]);
    $usuarioAntigo = $stmtAntigo->fetch(PDO::FETCH_ASSOC);

    if (!$usuarioAntigo) {
        echo "Usuário não encontrado.";
        exit;
    }

    $campos = [];
    $params = [':id' => $id];

    if ($nome) {
        $campos[] = "nome = :nome";
        $params[':nome'] = $nome;
    }

    if ($email) {
        $campos[] = "email = :email";
        $params[':email'] = $email;
    }

    if ($classe_id) {
        $campos[] = "classe_id = :classe_id";
        $params[':classe_id'] = $classe_id;
    }

    if ($area_id) {
        $campos[] = "area_id = :area_id";
        $params[':area_id'] = $area_id;
    }

    /* ==========================================
       ALTERAÇÃO DE SENHA SE "sim"
       ========================================== */
    $senhaTemporaria = null;

    if ($mudarSenha === 'sim') {

        $senhaTemporaria = bin2hex(random_bytes(4));
        $senhaHash = password_hash($senhaTemporaria, PASSWORD_DEFAULT);

        $campos[] = "senha = :senha";
        $campos[] = "primeiro_acesso = true";

        $params[':senha'] = $senhaHash;
    }

    if (!empty($campos)) {

        $sql = "UPDATE usuario SET " . implode(", ", $campos) . " WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        /* ==========================================
           ENVIO DE EMAIL SE SENHA FOI ALTERADA
           ========================================== */
        if ($mudarSenha === 'sim' && $senhaTemporaria) {

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
                $mail->addAddress($email ?? $usuarioAntigo['email'], $nome ?? $usuarioAntigo['nome']);

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
                error_log("Erro ao enviar email de redefinição: {$e->getMessage()}");
            }
        }

        echo "Alterações realizadas com sucesso!";
    } else {
        echo "Nenhum campo foi informado para atualização.";
    }

} catch (PDOException $e) {
    echo "Erro ao salvar alterações: " . $e->getMessage();
}