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
    </style>
</head>
<body>
    <div class="login-box">
        <h2>Login</h2>
        <p id="erro" class="erro"></p>
        <form id="loginForm">
            <input type="text" name="email" placeholder="Email" required>
            <input type="password" name="senha" placeholder="Senha" required>
            <button type="submit">Entrar</button>
        </form>
    </div>

    <script>
    const form = document.getElementById('loginForm');
    const erroBox = document.getElementById('erro');

    form.addEventListener('submit', async function(e) {
        e.preventDefault(); // impede envio padrão

        const formData = new FormData(form);

        try {
            const response = await fetch('../apis/auth.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                // Redireciona conforme grau_acesso
                switch (result.grau_acesso) {
                    case 1:
                        window.location.href = 'index.php';
                        break;
                    case 2:
                        window.location.href = 'index.php';
                        break;
                    case 3:
                        window.location.href = 'index.php';
                        break;
                    case 4:
                        window.location.href = 'index.php';
                        break;
                    default:
                        erroBox.textContent = "Nível de acesso inválido.";
                }
            } else {
                // Exibe mensagens de erro específicas
                if (result.error === "credenciais") {
                    erroBox.textContent = "Email ou senha inválidos.";
                } else if (result.error === "servidor") {
                    erroBox.textContent = "Erro interno no servidor.";
                } else if (result.error === "metodo_invalido") {
                    erroBox.textContent = "Método de requisição inválido.";
                } else {
                    erroBox.textContent = "Erro desconhecido.";
                }
            }
        } catch (error) {
            erroBox.textContent = "Erro de conexão com o servidor.";
            console.error(error);
        }
    });
</script>

</body>
</html>
