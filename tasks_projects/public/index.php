<?php
require_once "../config/connection.php";
session_start();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EMPARN - Portal</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./static/index.css">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <style>
        /* ====== ESTILOS DO PORTAL ====== */

        body {
            background: #f2f6fc;
        }

        /* Subheader com avatar */
        .profile-bar {
            background: #ffffff;
            border-bottom: 1px solid #e2e6ed;
            padding: 8px 24px;
            display: flex;
            justify-content: flex-end;
            align-items: center;
        }

        .user-avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: #0057d9;
            color: white;
            font-weight: bold;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 1.1rem;
        }

        /* CARDS DO PAINEL */
        .service-card {
            border: none;
            border-radius: 16px;
            padding: 25px 18px;
            color: white;
            cursor: pointer;
            transition: 0.25s;
            box-shadow: 0 8px 20px rgba(0,0,0,0.12);
        }

        .service-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 28px rgba(0,0,0,0.18);
        }

        .service-title {
            font-size: 1.1rem;
            font-weight: 600;
        }

        .service-icon {
            font-size: 2.2rem;
            margin-bottom: 8px;
            display: block;
        }

        /* TÍTULOS */
        .section-title {
            font-weight: bold;
            text-align: center;
            margin-bottom: 35px;
            margin-top: 50px;
            font-size: 2rem;
            color: #333;
        }

        /* Rodapé */
        footer {
            margin-top: 60px;
            background: #111;
            color: #ddd;
        }
    </style>
</head>
<body>

    <!-- HEADER -->
    <?php include __DIR__ . '/../templates/header.php'; ?>

    <!-- SUBHEADER -->
    <div class="profile-bar">
        <div class="dropdown d-flex align-items-center">
            <div class="user-avatar me-2">
                <?= strtoupper(substr($_SESSION['usuario_nome'] ?? 'U', 0, 1)); ?>
            </div>

            <a class="dropdown-toggle text-secondary" href="#" id="userMenu" data-bs-toggle="dropdown"></a>

            <ul class="dropdown-menu dropdown-menu-end">
                <li class="dropdown-item"><strong>Nome:</strong> <?= htmlspecialchars($_SESSION['usuario_nome'] ?? ''); ?></li>
                <li class="dropdown-item"><strong>Classe:</strong> <?= htmlspecialchars($_SESSION['classe_nome'] ?? ''); ?></li>
                <li class="dropdown-item"><strong>Área:</strong> <?= htmlspecialchars($_SESSION['grau_acesso'] ?? ''); ?></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="logout.php">Sair</a></li>
            </ul>
        </div>
    </div>

    <!-- CARROSSEL DE NOTÍCIAS -->
    <?php include __DIR__ . '/../templates/carrossel_noticias.php'; ?>

    <!-- PAINEL DE SERVIÇOS -->
    <div class="container">
        <h2 class="section-title">Painel de Serviços</h2>

        <div class="row row-cols-1 row-cols-md-4 g-4">

            <!-- Card fixo 1 -->
            <div class="col">
                <a href="documents_inst.php" class="text-decoration-none">
                    <div class="service-card" style="background:#0057d9;">
                        <i class="bi bi-folder2-open service-icon"></i>
                        <div class="service-title">Documentos Institucionais</div>
                    </div>
                </a>
            </div>

            <!-- Card fixo 2 -->
            <div class="col">
                <a href="../controle/index_controle.php" class="text-decoration-none">
                    <div class="service-card" style="background:#d90429;">
                        <i class="bi bi-gear-fill service-icon"></i>
                        <div class="service-title">Controle</div>
                    </div>
                </a>
            </div>

            <!-- Cards vindos do banco -->
            <?php
            $sql_cards = "SELECT * FROM dashboard ORDER BY id DESC";
            $result_cards = $pdo->query($sql_cards)->fetchAll(PDO::FETCH_ASSOC);

            if ($result_cards):
                foreach ($result_cards as $card): ?>
                    <div class="col">
                        <a href="<?= htmlspecialchars($card['link']) ?>" target="_blank" class="text-decoration-none">
                            <div class="service-card" style="background:<?= htmlspecialchars($card['cor']) ?>;">
                                <i class="service-icon dynamic-icon"></i>
                                <div class="service-title"><?= htmlspecialchars($card['titulo']) ?></div>
                            </div>
                        </a>
                    </div>
                <?php endforeach;
            endif; ?>
        </div>
    </div>

    <!-- HISTÓRICO DE NOTÍCIAS -->
    <?php include __DIR__ . '/../templates/historico_noticias.php'; ?>

    <!-- BOTÃO TODAS AS NOTÍCIAS -->
    <div class="container text-center mt-4 mb-5">
        <a href="todas_as_noticias.php" class="btn btn-primary btn-lg px-4 py-2 shadow-lg rounded-pill">
            <i class="bi bi-newspaper"></i> Ver todas as notícias
        </a>
    </div>

    <!-- RODAPÉ -->
    <footer class="text-center py-3">
        <i class="bi bi-at"></i> <?= date('Y') ?> EMPARN — Todos os direitos reservados.
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- SCRIPT PARA ÍCONES INTELIGENTES -->
    <script>
    document.addEventListener("DOMContentLoaded", () => {

        const iconMap = [
            { keywords: ["documento", "arquivo", "pdf", "doc"], icon: "bi-file-earmark-text" },
            { keywords: ["controle", "administração", "gerenciar", "config"], icon: "bi-gear-fill" },
            { keywords: ["relatório", "report"], icon: "bi-bar-chart-fill" },
            { keywords: ["notícia", "informação"], icon: "bi-newspaper" },
            { keywords: ["usuário", "perfil", "colaborador"], icon: "bi-person-circle" },
            { keywords: ["financeiro", "dinheiro", "pagamento"], icon: "bi-cash-stack" },
            { keywords: ["estatística", "dados", "analise"], icon: "bi-graph-up" },
            { keywords: ["suporte", "ajuda"], icon: "bi-life-preserver" },
            { keywords: ["setor", "departamento"], icon: "bi-diagram-3-fill" },
            { keywords: ["formulário", "form"], icon: "bi-ui-checks-grid" },
            { keywords: ["projeto", "project"], icon: "bi-kanban" },
            { keywords: ["agenda", "calendário"], icon: "bi-calendar-event" },
            { keywords: ["email", "mensagem", "comunicação"], icon: "bi-envelope-paper-fill" }
        ];

        const defaultIcon = "bi-grid-3x3-gap-fill";

        document.querySelectorAll(".dynamic-icon").forEach(icon => {
            const card = icon.closest(".service-card");
            const title = card.querySelector(".service-title").innerText.toLowerCase();

            let chosenIcon = defaultIcon;
            for (const item of iconMap) {
                if (item.keywords.some(k => title.includes(k))) {
                    chosenIcon = item.icon;
                    break;
                }
            }

            icon.classList.add("bi", chosenIcon);
        });

    });
    </script>

</body>
</html>
