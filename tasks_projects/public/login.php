<?php
require_once '../config/connection.php';
session_start();

// Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    try {
        // Consulta segura usando prepared statements (PDO)
        $stmt = $pdo->prepare("SELECT * FROM usuario WHERE email = :email AND senha = :senha");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':senha', $senha, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() === 1) {
            $_SESSION['email'] = $email;
            header("Location: dashboard.php"); // Redireciona para área restrita
            exit();
        } else {
            $erro = "Usuário ou senha inválidos!";
        }
    } catch (PDOException $e) {
        $erro = "Erro na consulta: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-box {
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px #aaa;
            width: 320px;
            display: flex;
            flex-direction: column;
            align-items: center; /* centraliza conteúdo interno */
        }
        .login-box h2 {
            text-align: center;
            margin-bottom: 20px;
            width: 100%;
        }
        .login-box form {
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center; /* centraliza inputs e botão */
        }
        .login-box input {
            width: 90%; /* deixa uma margem lateral */
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            text-align: center; /* texto centralizado dentro do input */
        }
        .login-box button {
            width: 95%;
            padding: 10px;
            margin-top: 15px;
            background: #007bff;
            border: none;
            color: #fff;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }
        .login-box button:hover {
            background: #0056b3;
        }
        .erro {
            color: red;
            text-align: center;
            margin-bottom: 10px;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>Login</h2>
        <?php if (!empty($erro)) echo "<p class='erro'>$erro</p>"; ?>
        <form method="POST" action="">
            <input type="text" name="email" placeholder="Email" required>
            <input type="password" name="senha" placeholder="Senha" required>
            <button type="submit">Entrar</button>
        </form>
    </div>
</body>
</html>