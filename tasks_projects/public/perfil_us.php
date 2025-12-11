<?php
session_start();

// Bloquear acesso caso o usuário não esteja logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

require_once "../config/connection.php";

// ID do usuário logado
$usuario_id = $_SESSION['usuario_id'];

// ----- BUSCAR DADOS DO USUÁRIO -----

$sql = "SELECT 
            u.id, u.nome, u.email, u.classe_id, u.area_id,
            c.nome AS classe,
            a.nome AS area,
            u.data_criacao, u.data_modificacao
        FROM usuario u
        INNER JOIN classe_usuario c ON c.id = u.classe_id
        INNER JOIN area_atuacao a ON a.id = u.area_id
        WHERE u.id = :id";

$stmt = $pdo->prepare($sql);
$stmt->bindParam(":id", $usuario_id, PDO::PARAM_INT);
$stmt->execute();
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

// Segurança extra
if (!$usuario) {
    die("Usuário não encontrado.");
}

// Criar iniciais
$partes = explode(" ", $usuario["nome"]);
$iniciais = strtoupper($partes[0][0] . ($partes[1][0] ?? ""));
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Perfil</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./static/index.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <style>
        body {
            background: #f2f6fc;
        }

        .profile-container {
            margin-top: 60px;
            max-width: 850px;
        }

        .profile-card {
            background: #fff;
            border-radius: 16px;
            padding: 30px 40px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.08);
        }

        .avatar-big {
            width: 100px;
            height: 100px;
            background: #0057d9;
            border-radius: 50%;
            color: #fff;
            font-size: 2.5rem;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: auto;
            border: 4px solid white;
            box-shadow: 0 0 10px rgba(0,0,0,0.15);
        }

        .profile-title {
            font-size: 1.8rem;
            font-weight: bold;
            text-align: center;
            margin-top: 18px;
        }

        .info-label {
            font-weight: 600;
            color: #555;
        }

        .info-value {
            font-size: 1rem;
            color: #222;
        }

        .divider {
            border-bottom: 1px solid #e6e6e6;
            margin: 25px 0;
        }

        .btn-back {
            margin-top: 30px;
        }
    </style>

</head>
<body>

    <?php include __DIR__ . '/../templates/header.php'; ?>

    <div class="container profile-container">

        <div class="profile-card">

            <!-- Avatar central -->
            <div class="avatar-big"><?= $iniciais ?></div>

            <!-- Nome do usuário -->
            <div class="profile-title"><?= htmlspecialchars($usuario["nome"]) ?></div>

            <div class="divider"></div>

            <!-- Informações -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <p class="info-label">Email:</p>
                    <p class="info-value"><?= htmlspecialchars($usuario["email"]) ?></p>
                </div>
                <div class="col-md-6">
                    <p class="info-label">Classe:</p>
                    <p class="info-value"><?= htmlspecialchars($usuario["classe"]); ?></p>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <p class="info-label">Área de Atuação:</p>
                    <p class="info-value"><?= htmlspecialchars($usuario["area"]); ?></p>
                </div>
                <div class="col-md-6">
                    <p class="info-label">ID do Usuário:</p>
                    <p class="info-value">#<?= $usuario["id"] ?></p>
                </div>
            </div>

            <div class="divider"></div>

            <div class="row">
                <div class="col-md-6">
                    <p class="info-label">Criado em:</p>
                    <p class="info-value"><?= $usuario["data_criacao"] ?></p>
                </div>
                <div class="col-md-6">
                    <p class="info-label">Última Modificação:</p>
                    <p class="info-value"><?= $usuario["data_modificacao"] ?></p>
                </div>
            </div>

            <!-- Botão de voltar -->
            <div class="text-center btn-back">
                <a href="home.php" class="btn btn-primary px-4 py-2 rounded-pill">
                    <i class="bi bi-arrow-left"></i> Voltar ao Painel
                </a>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
