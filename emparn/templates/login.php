

<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    $sql = "SELECT * FROM usuarios WHERE email = ? AND senha = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $senha);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $_SESSION['usuario'] = $email;
        header("Location: painel.php");
        exit();
    } else {
        $erro = "Email ou senha inválidos.";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="../static/login.css">
</head>
<body>

    <header class="header">
        <div class="titulo">
            <h1>EMPARN</h1>
        </div>
    </header>

    <div class="boxLogin">
        <h1>Login</h1>

        <?php if (isset($erro)) echo "<p class='erro'>$erro</p>"; ?>

        <form method="POST" class="caixa-form">
            <div class="emial">
                <label for="email">Email</label><br>
                <input type="email" name="email" required>
            </div>

            <div class="password">
                <label for="password">Senha</label><br>
                <input type="password" name="senha" required maxlength="5">
            </div>

            <button type="submit">Validar</button>

            <div class="links">
                <a href="/redefinir">Esqueceu a senha?</a><br>
            </div>
        </form>
    </div>
    
</body>
</html>
