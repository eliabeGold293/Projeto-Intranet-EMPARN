<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login");
    exit;
}

require_once __DIR__ . '/../config/connection.php';

/*
|--------------------------------------------------------------------------
| BUSCAR TODOS OS PROJETOS
|--------------------------------------------------------------------------
*/

$sqlProjetos = "
    SELECT 
        p.*,
        u.nome AS criador
    FROM projeto p
    LEFT JOIN usuario u ON u.id = p.criado_por
    ORDER BY p.data_criacao DESC
";

$stmt = $pdo->prepare($sqlProjetos);
$stmt->execute();
$projetos = $stmt->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| ARRAY PARA GUARDAR MODAIS DE TAREFA (FORA DO MODAL DO PROJETO)
|--------------------------------------------------------------------------
*/
$modaisTarefa = [];
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projetos</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background-color: #f4f7fb;
        }

        .card-projeto {
            transition: 0.3s;
            cursor: pointer;
        }

        .card-projeto:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body>
    <?php if (isset($_SESSION['alerta'])): ?>

        <div id="alerta-sistema" class="alert alert-<?= $_SESSION['alerta']['tipo'] ?> alert-dismissible fade show" role="alert">
            <?= $_SESSION['alerta']['mensagem'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>

    <?php unset($_SESSION['alerta']);
    endif; ?>

    <?php include __DIR__ . '/../templates/header.php'; ?>

    <div class="container mt-5">
        <div class="container mt-3">
            <a href="javascript:history.back();" class="btn btn-secondary mb-3">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>
                <i class="bi bi-folder-fill text-primary"></i> Projetos
            </h2>
        </div>

        <div class="row g-4">

            <?php foreach ($projetos as $projeto): ?>

                <?php
                /* BUSCAR MEMBROS */
                $sqlMembros = "
                    SELECT u.nome, pp.nome AS papel
                    FROM projeto_usuario pu
                    INNER JOIN usuario u ON u.id = pu.usuario_id
                    INNER JOIN papel_projeto pp ON pp.id = pu.papel_id
                    WHERE pu.projeto_id = :projeto_id
                ";
                $stmtMembros = $pdo->prepare($sqlMembros);
                $stmtMembros->bindParam(":projeto_id", $projeto['id'], PDO::PARAM_INT);
                $stmtMembros->execute();
                $membros = $stmtMembros->fetchAll(PDO::FETCH_ASSOC);

                /* BUSCAR TAREFAS */
                $sqlTarefas = "
                    SELECT * FROM tarefa
                    WHERE projeto_id = :projeto_id
                    ORDER BY data_criacao DESC
                ";
                $stmtTarefas = $pdo->prepare($sqlTarefas);
                $stmtTarefas->bindParam(":projeto_id", $projeto['id'], PDO::PARAM_INT);
                $stmtTarefas->execute();
                $tarefas = $stmtTarefas->fetchAll(PDO::FETCH_ASSOC);
                ?>

                <div class="col-md-4">
                    <div class="card card-projeto h-100"
                        data-bs-toggle="modal"
                        data-bs-target="#modalProjeto<?= $projeto['id'] ?>">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="bi bi-kanban-fill text-secondary"></i>
                                <?= htmlspecialchars($projeto['titulo']) ?>
                            </h5>

                            <p class="card-text text-muted">
                                <?= substr(htmlspecialchars($projeto['descricao']), 0, 100) ?>...
                            </p>

                            <span class="badge bg-success">
                                <?= htmlspecialchars($projeto['status']) ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- MODAL DO PROJETO -->
                <div class="modal fade" id="modalProjeto<?= $projeto['id'] ?>" tabindex="-1">
                    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                        <div class="modal-content">

                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title">
                                    <i class="bi bi-folder2-open"></i>
                                    <?= htmlspecialchars($projeto['titulo']) ?>
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body">

                                <div class="row mb-4">
                                    <div class="col-md-8">
                                        <h6>Descrição</h6>
                                        <p><?= nl2br(htmlspecialchars($projeto['descricao'])) ?></p>
                                    </div>

                                    <div class="col-md-4">
                                        <p>
                                            <strong>Status:</strong>
                                            <span class="badge bg-success">
                                                <?= htmlspecialchars($projeto['status']) ?>
                                            </span>
                                        </p>
                                        <p><strong>Criado por:</strong> <?= htmlspecialchars($projeto['criador']) ?></p>
                                        <p><strong>Data início:</strong> <?= date('d/m/Y', strtotime($projeto['data_inicio'])) ?></p>
                                        <p><strong>Data fim:</strong> <?= date('d/m/Y', strtotime($projeto['data_fim'])) ?></p>
                                    </div>
                                </div>

                                <hr>

                                <h5>
                                    <i class="bi bi-people-fill"></i> Membros
                                </h5>

                                <?php if ($membros): ?>
                                    <ul class="list-group mb-4">
                                        <?php foreach ($membros as $m): ?>
                                            <li class="list-group-item d-flex justify-content-between">
                                                <?= htmlspecialchars($m['nome']) ?>
                                                <span class="badge bg-secondary">
                                                    <?= htmlspecialchars($m['papel']) ?>
                                                </span>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <p class="text-muted">Nenhum membro neste projeto.</p>
                                <?php endif; ?>

                                <h5>
                                    <i class="bi bi-list-task"></i> Tarefas
                                </h5>

                                <?php if ($tarefas): ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Título</th>
                                                    <th>Status</th>
                                                    <th>Prazo</th>
                                                    <th>Arquivo</th>
                                                    <th>Informação</th>
                                                    <th>Prorrogar</th>
                                                </tr>
                                            </thead>
                                            <tbody>

                                                <?php foreach ($tarefas as $t): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($t['titulo']) ?></td>

                                                        <td>
                                                            <span class="badge bg-info">
                                                                <?= htmlspecialchars($t['status']) ?>
                                                            </span>
                                                        </td>

                                                        <td>
                                                            <?= $t['prazo'] ? date('d/m/Y', strtotime($t['prazo'])) : '—' ?>
                                                        </td>

                                                        <td>
                                                            <?php if (!empty($t['arquivo'])): ?>
                                                                <a href="<?= htmlspecialchars($t['arquivo']) ?>"
                                                                    target="_blank"
                                                                    class="btn btn-sm btn-outline-primary">
                                                                    <i class="bi bi-paperclip"></i> Ver Arquivo
                                                                </a>
                                                            <?php else: ?>
                                                                <span class="text-muted">Sem arquivo</span>
                                                            <?php endif; ?>
                                                        </td>

                                                        <td>
                                                            <button onclick="abrirModalTarefa(<?= $t['id'] ?>)">
                                                                <i class="bi bi-info-circle"></i>
                                                            </button>
                                                        </td>

                                                        <td>
                                                            <button class="btn btn-sm btn-warning"
                                                                onclick="abrirModalProrrogar(<?= $t['id'] ?>)">
                                                                <i class="bi bi-clock-history"></i> Prorrogar
                                                            </button>
                                                        </td>
                                                    </tr>

                                                    <?php
                                                    $modaisTarefa[] = '
                                                        <div class="modal fade" id="modalProrrogar' . $t['id'] . '" tabindex="-1">
                                                        <div class="modal-dialog modal-dialog-centered">
                                                        <div class="modal-content">

                                                        <div class="modal-header bg-warning">
                                                        <h5 class="modal-title">
                                                        <i class="bi bi-clock"></i> Prorrogar prazo
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>

                                                        <form method="POST" action="prorrogar-tarefa">

                                                        <div class="modal-body">

                                                        <input type="hidden" name="tarefa_id" value="' . $t['id'] . '">

                                                        <label class="form-label">Novo prazo</label>

                                                        <input type="date" 
                                                        name="novo_prazo" 
                                                        class="form-control" 
                                                        required>

                                                        </div>

                                                        <div class="modal-footer">

                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                        Cancelar
                                                        </button>

                                                        <button type="submit" class="btn btn-warning">
                                                        Salvar novo prazo
                                                        </button>

                                                        <a href="prorrogar-tarefa">Clique aqui</a>

                                                        </div>

                                                        </form>

                                                        </div>
                                                        </div>
                                                        </div>';
                                                    ?>

                                                <?php endforeach; ?>

                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted">Nenhuma tarefa cadastrada.</p>
                                <?php endif; ?>

                            </div>
                        </div>
                    </div>
                </div>

            <?php endforeach; ?>

        </div>
    </div>

    <!-- RENDERIZA TODOS OS MODAIS DE TAREFA FORA -->
    <?php foreach ($modaisTarefa as $modal): ?>
        <?= $modal ?>
    <?php endforeach; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function abrirModalTarefa(id) {

            const modalElement = document.getElementById('modalTarefa' + id);

            const modal = new bootstrap.Modal(modalElement, {
                backdrop: true,
                focus: true
            });

            modal.show();

            // Corrige problema de aria-hidden no modal pai
            const modalProjeto = document.querySelector('.modal.show');
            if (modalProjeto) {
                modalProjeto.removeAttribute('aria-hidden');
            }
        }

        function abrirModalProrrogar(id) {

            const modalElement = document.getElementById('modalProrrogar' + id);

            const modal = new bootstrap.Modal(modalElement);

            modal.show();
        }


        setTimeout(function() {

            let alerta = document.getElementById("alerta-sistema");

            if (alerta) {

                let bsAlert = new bootstrap.Alert(alerta);
                bsAlert.close();

            }

        }, 2500);

    </script>

</body>

</html>