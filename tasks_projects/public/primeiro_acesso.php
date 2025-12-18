<?php
$email = isset($_GET['email']) ? htmlspecialchars($_GET['email']) : '';
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Primeiro Acesso - EMPARN</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            height: 100vh;
            margin: 0;
            overflow: hidden;
            background: linear-gradient(135deg, #0d47a1, #283593, #1e3c72);
            background-size: 250% 250%;
            animation: gradientMove 14s ease infinite;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #fff;
        }

        @keyframes gradientMove {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .bg-glow {
            position: absolute;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(255,255,255,0.10) 0%, rgba(255,255,255,0) 70%);
            filter: blur(80px);
            animation: glowMove 10s infinite alternate ease-in-out;
            opacity: 0.6;
        }

        /* Card moderno */
        .card-custom {
            border-radius: 18px;
            padding: 35px;
            background: #ffffff;
            box-shadow: 0 8px 35px rgba(0,0,0,0.18);
            max-width: 480px;
            margin: auto;
            animation: fadeInUp 0.6s ease forwards;
            opacity: 0;
            transform: translateY(20px);
        }

        /* Animação suave */
        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Título corporativo */
        .brand-title {
            font-weight: 800;
            color: #1e3c72;
            letter-spacing: 1px;
            margin-bottom: 12px;
            font-size: 1.6rem;
        }

        /* Subtítulo mais elegante */
        .subtitle {
            color: #555;
            font-size: 0.95rem;
        }

        /* Inputs com estilo profissional */
        .form-control {
            padding: 12px;
            border-radius: 10px;
            border: 1.5px solid #d4d4d4;
            transition: all 0.25s ease;
            font-size: 1rem;
        }

        .form-control:focus {
            border-color: #1e3c72;
            box-shadow: 0 0 0 3px rgba(30, 60, 114, 0.18);
        }

        /* Label mais clara */
        .form-label {
            font-weight: 600;
            color: #1e3c72;
        }

        /* Botão premium */
        .btn-custom {
            background: #1e3c72;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            padding: 12px;
            font-weight: 600;
            transition: 0.25s ease;
        }

        .btn-custom:hover {
            background: #283593;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0,0,0,0.2);
        }

        /* Mensagens */
        #msg {
            font-size: 0.95rem;
        }

        /* Ajusta o fundo atrás do card */
        .fade-card {
            position: relative;
            z-index: 3;
        }
    </style>
</head>

<body>

<!-- GLOW corporativo -->
<div class="bg-glow"></div>

<div class="container fade-card" id="cardWrapper">
    <div class="row justify-content-center">
        <div class="col-md-5">

            <!-- LOGO EMPARN -->
            <h1 class="text-center text-white fw-bold mb-4" style="letter-spacing:2px;">
                EMPARN
            </h1>

            <!-- CARD -->
            <div class="card card-custom">
                <h3 class="brand-title text-center">Primeiro Acesso</h3>
                <p class="subtitle text-center mb-4">Crie sua senha definitiva para acessar o sistema.</p>

                <div id="msg" class="text-center mb-2 fw-semibold"></div>

                <form id="firstAccessForm" novalidate>
                    <input type="hidden" name="email" value="<?= $email ?>">

                    <div class="mb-3">
                        <label class="form-label">Nova Senha</label>
                        <input type="password" id="senha1" name="senha1" 
                               class="form-control" placeholder="Digite sua nova senha" required>
                        <div class="invalid-feedback">Preencha este campo.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Confirmar Nova Senha</label>
                        <input type="password" id="senha2" name="senha2" 
                               class="form-control" placeholder="Confirme sua nova senha" required>
                        <div class="invalid-feedback">As senhas não coincidem.</div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 btn-custom py-2">
                        Salvar Senha
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>


<!-- Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    document.getElementById('firstAccessForm').addEventListener('submit', async e => {
        e.preventDefault();

        const form = e.target;
        const senha1 = document.getElementById("senha1");
        const senha2 = document.getElementById("senha2");
        const msg = document.getElementById("msg");

        // Remove estados anteriores
        senha1.classList.remove("is-invalid");
        senha2.classList.remove("is-invalid");
        form.classList.remove("was-validated");

        // Validação manual real
        if (senha1.value.trim() === "" || senha2.value.trim() === "") {
            msg.style.color = "red";
            msg.textContent = "Preencha todos os campos.";
            return;
        }

        if (senha1.value.trim() !== senha2.value.trim()) {
            senha2.classList.add("is-invalid");
            msg.style.color = "red";
            msg.textContent = "As senhas não coincidem.";
            return;
        }

        // Mensagem temporária
        msg.textContent = "Processando...";
        msg.style.color = "#0d6efd";

        const formData = new FormData(form);

        const res = await fetch("salvar-primeiro-acesso", {
            method: "POST",
            body: formData
        });

        const json = await res.json();

        if (json.success) {
            msg.style.color = "green";
            msg.textContent = json.message;

            setTimeout(() => {
                window.location.href = "login";
            }, 1500);

        } else {
            msg.style.color = "red";
            msg.textContent = json.message;
        }
    });

</script>

</body>
</html>
