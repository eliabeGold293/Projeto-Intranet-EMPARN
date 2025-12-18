<?php
session_start();

$erro = $_SESSION['erro_login'] ?? null;
$info = $_SESSION['info_login'] ?? null;

unset($_SESSION['erro_login'], $_SESSION['info_login']);
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
            align-items: center;
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
            align-items: center;
        }
        .login-box input {
            width: 90%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            text-align: center;
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
        .info {
            color: #0c5460;
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            padding: 8px;
            border-radius: 5px;
            text-align: center;
            margin-bottom: 10px;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>Login</h2>

        <?php if ($erro): ?>
            <p class="erro"><?= htmlspecialchars($erro) ?></p>
        <?php endif; ?>

        <form action="auth" method="post">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="senha" placeholder="Senha" required>
            <button type="submit">Entrar</button>
        </form>

        <small style="margin-top:10px; color:#666; text-align:center;">
            Caso seja seu primeiro acesso, você será direcionado para a configuração inicial.
        </small>
    </div>
</body>
</html>
