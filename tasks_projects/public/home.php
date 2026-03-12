<?php
session_start();

$grauAcesso = $_SESSION['grau_acesso'] ?? 0;
$emailUsuario = $_SESSION['usuario_email'] ?? '';
$classeNome = $_SESSION['classe_nome'] ?? '';
// Impedir cache da página protegida
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Impedir navegação "voltar" após logout
header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");

// Se não estiver logado → volta para login
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['grau_acesso'])) {
    header("Location: login.php");
    # echo 'Não há usuário logado';
    exit;
}

require_once __DIR__ . '/../config/connection.php';

$stmt = $pdo->prepare("
    SELECT u.email, c.nome AS classe_nome
    FROM usuario u
    JOIN classe_usuario c ON c.id = u.classe_id
    WHERE u.id = :id
");

$stmt->execute([
    ':id' => $_SESSION['usuario_id']
]);

$usuarioDados = $stmt->fetch(PDO::FETCH_ASSOC);

$emailUsuario = $usuarioDados['email'] ?? '';
$classeNome = $usuarioDados['classe_nome'] ?? '';

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EMPARN - Portal</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <style>
        /* ====== ESTILOS DO PORTAL ====== */

        body {
            background: #f2f6fc;
        }


        /* DROPDOWN SIMPLES E ELEGANTE */
        .dropdown-menu {
            width: 220px !important;
            border-radius: 12px;
            padding: 8px;
            margin-top: 12px !important;
            border: 1px solid #e6e6e6;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
        }

        .dropdown-item {
            font-size: 0.85rem;
            padding: 6px 10px;
            border-radius: 6px;
            transition: background 0.15s;
        }

        .dropdown-item:hover {
            background: #f1f4f9;
        }

        .dropdown-item-title {
            font-size: 0.90rem;
            font-weight: 600;
        }

        .info-small {
            font-size: 0.80rem;
            color: #777;
            margin-bottom: 12px;
        }

        .section-title {
            font-size: 0.75rem;
            font-weight: bold;
            color: #555;
            text-transform: uppercase;
            margin-top: 10px;
            margin-bottom: 6px;
        }

        .info-box {
            background: #f8f9fc;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #eaeaea;
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

        .user-avatar.dropdown-toggle::after {
            display: none !important;
        }

        /* AVATAR DO USUÁRIO CORRIGIDO */
        .user-avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: #0057d9;
            color: white !important;
            font-weight: bold;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 1.2rem !important;
            line-height: 42px !important;
            text-align: center !important;
            border: 2px solid #ffffff;
            /* Borda branca elegante */
            cursor: pointer;
        }

        /* CARDS DO PAINEL */
        .service-card {
            border: none;
            border-radius: 16px;
            padding: 25px 18px;
            color: white;
            cursor: pointer;
            transition: 0.25s;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        }

        .service-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.18);
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

        .card-img-top {
            height: 200px;
            object-fit: cover;
        }

        /* Limita texto em múltiplas linhas */
        .text-truncate-multiline {
            display: -webkit-box;
            -webkit-box-orient: vertical;
            overflow: hidden;
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

    <!-- Resposta para a classe JUNIOR -->
    <?php if ($grauAcesso == 1): ?>

        <div class="container mt-3">
            <div class="alert alert-danger text-center">
                Infelizmente você não tem acesso a este recurso,
                caso haja dúvidas, contatar o administrador do sistema.
            </div>
        </div>

    <?php endif; ?>

    <!-- SUBHEADER -->
    <div class="profile-bar">

        <div class="dropdown">

            <?php
            $partesNome = explode(" ", trim($_SESSION["usuario_nome"]));
            $iniciais = strtoupper($partesNome[0][0] . ($partesNome[1][0] ?? ''));
            ?>

            <div class="user-avatar dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                <?= $iniciais ?>
            </div>

            <div class="dropdown-menu dropdown-menu-end">

                <!-- Cabeçalho -->
                <div class="dropdown-item-title text-center mb-2">
                    <?php echo $_SESSION["usuario_nome"]; ?>
                </div>

                <hr class="dropdown-divider">

                <!-- Links simples com ícones -->
                <a class="dropdown-item d-flex align-items-center" href="perfil-us">
                    <i class="bi bi-person me-2"></i> Meu Perfil
                </a>

                <a class="dropdown-item d-flex align-items-center" href="logout">
                    <i class="bi bi-box-arrow-right me-2"></i> Sair
                </a>
            </div>
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
                <a href="documentos-institucionais" class="text-decoration-none">
                    <div class="service-card" style="background:#0057d9;">
                        <i class="bi bi-folder2-open service-icon"></i>
                        <div class="service-title">DOCUMENTOS INSTITUCIONAIS</div>
                    </div>
                </a>
            </div>

            <!-- Card fixo 2 -->
            <div class="col">
                <a href="#" id="btnControle" class="text-decoration-none">
                    <div class="service-card" style="background:#d90429;">
                        <i class="bi bi-gear-fill service-icon"></i>
                        <div class="service-title">ÁREA DE CONTROLE</div>
                    </div>
                </a>
            </div>

            <!-- Card fixo 3 -->
            <div class="col">
                <a href="view-projetos" class="text-decoration-none">
                    <div class="service-card" style="background:#d90429;">
                        <i class="bi bi-kanban-fill service-icon"></i>
                        <div class="service-title">PROJETOS & TAREFAS</div>
                    </div>
                </a>
            </div>

            <!-- Card fixo 3 -->
            <div class="col">
                <a href="uploads-usuarios" class="text-decoration-none">
                    <div class="service-card" style="background:#d90429;">
                        <i class="bi bi-upload service-icon"></i>
                        <div class="service-title">UPLOAD DE ARQUIVOS</div>
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
        <a href="todas-as-noticias" class="btn btn-primary btn-lg px-4 py-2 shadow-lg rounded-pill">
            <i class="bi bi-newspaper"></i> Ver todas as notícias
        </a>
    </div>

    <!-- RODAPÉ -->
    <footer class="text-center py-3">
        <i class="bi bi-at"></i> <?= date('Y') ?> EMPARN — Todos os direitos reservados.
    </footer>

    <div class="modal fade" id="modalControle" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Confirmação de Segurança</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <div id="erroSenha" class="alert alert-danger d-none"></div>

                    <div class="mb-3">
                        <label>Email</label>
                        <input type="email" class="form-control"
                            value="<?= htmlspecialchars($emailUsuario) ?>"
                            readonly>
                    </div>

                    <div class="mb-3">
                        <label>Classe</label>
                        <input type="text" class="form-control"
                            value="<?= htmlspecialchars($classeNome) ?>"
                            readonly>
                    </div>

                    <div class="mb-3">
                        <label>Confirme sua senha</label>
                        <input type="password" id="senhaConfirmacao" class="form-control">
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-primary" id="confirmarControle">
                        Confirmar acesso
                    </button>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- SCRIPT PARA ÍCONES INTELIGENTES -->
    <script>
        document.addEventListener("DOMContentLoaded", () => {

            const iconMap = [{
                    keywords: ["documento", "arquivo", "pdf", "doc"],
                    icon: "bi-file-earmark-text"
                },
                {
                    keywords: ["controle", "administração", "gerenciar", "config"],
                    icon: "bi-gear-fill"
                },
                {
                    keywords: ["relatório", "report"],
                    icon: "bi-bar-chart-fill"
                },
                {
                    keywords: ["notícia", "informação"],
                    icon: "bi-newspaper"
                },
                {
                    keywords: ["usuário", "perfil", "colaborador"],
                    icon: "bi-person-circle"
                },
                {
                    keywords: ["financeiro", "dinheiro", "pagamento"],
                    icon: "bi-cash-stack"
                },
                {
                    keywords: ["estatística", "dados", "analise"],
                    icon: "bi-graph-up"
                },
                {
                    keywords: ["suporte", "ajuda"],
                    icon: "bi-life-preserver"
                },
                {
                    keywords: ["setor", "departamento"],
                    icon: "bi-diagram-3-fill"
                },
                {
                    keywords: ["formulário", "form"],
                    icon: "bi-ui-checks-grid"
                },
                {
                    keywords: ["projeto", "project"],
                    icon: "bi-kanban"
                },
                {
                    keywords: ["agenda", "calendário"],
                    icon: "bi-calendar-event"
                },
                {
                    keywords: ["email", "mensagem", "comunicação"],
                    icon: "bi-envelope-paper-fill"
                }
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

        const grauAcesso = <?= $grauAcesso ?>;

        document.getElementById("btnControle").addEventListener("click", function(e) {

            e.preventDefault();

            switch (grauAcesso) {

                case 1:
                    window.scrollTo(0, 0);
                    return;

                default:
                    const modal = new bootstrap.Modal(
                        document.getElementById("modalControle")
                    );
                    modal.show();
            }

        });


        document.getElementById("confirmarControle")
            .addEventListener("click", async () => {

                const senha = document.getElementById("senhaConfirmacao").value;

                const form = new FormData();
                form.append("senha", senha);

                const response = await fetch("confirmar-controle", {
                    method: "POST",
                    body: form
                });

                const data = await response.json();

                if (data.status === "error") {

                    const erro = document.getElementById("erroSenha");
                    erro.classList.remove("d-none");
                    erro.innerText = data.message;
                    return;

                }

                switch (data.grau_acesso) {

                    case 2:
                        window.location.href = "controle-projetos";
                        break;

                    case 3:
                        window.location.href = "controle-projetos";
                        break;

                    case 4:
                        window.location.href = "control";
                        break;

                    default:
                        alert("Acesso não autorizado");
                }

            });
    </script>
</body>

</html>