<?php
require_once "../config/connection.php";

try {
    $stmt = $pdo->prepare("
        SELECT u.id,
               u.nome,
               u.email,
               u.classe_id,
               u.area_id,
               c.nome AS classe_nome,
               a.nome AS area_nome,
               u.data_criacao,
               u.data_modificacao
        FROM usuario u
        JOIN classe_usuario c ON u.classe_id = c.id
        JOIN area_atuacao a   ON u.area_id   = a.id
        ORDER BY u.id ASC
    ");
    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro: " . $e->getMessage());
}

try {
    $stmt = $pdo->prepare("SELECT id, nome FROM classe_usuario ORDER BY nome ASC");
    $stmt->execute();
    $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("SELECT id, nome FROM area_atuacao ORDER BY nome ASC");
    $stmt->execute();
    $areas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro ao carregar selects: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Lista de Usuários</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">


    <style>
        body {
        background-color: #eef1f4;
        display: flex;
        margin: 0;
        font-family: 'Segoe UI', Arial, sans-serif;
    }

    /* Área principal agora mais larga e confortável */
    .main-content {
        flex: 1;
        padding: 40px;
        margin-left: 250px;
        max-width: 1600px;      /* AUMENTO DO WIDTH TOTAL */
        margin-right: auto;
    }

    @media (max-width: 768px) {
        .main-content {
            margin-left: 0;
            padding: 20px;
        }
    }

    /* Título principal */
    .page-title {
        font-size: 2rem;
        font-weight: 700;
        color: #0046a0;
        margin-bottom: 25px;
    }

    /* Painéis modernos */
    .card-modern {
        background: #fff;
        border-radius: 12px;
        padding: 28px;
        border: none;
        max-width: 1300px;      /* AQUI AMPLIEI O CARD */
        margin: 0 auto;
        box-shadow: 0 4px 14px rgba(0,0,0,0.08);
    }

    .card-header-modern {
        background: transparent !important;
        border-bottom: 2px solid #e6e9ed;
        padding-bottom: 12px;
        margin-bottom: 18px;
    }

    /* Labels */
    .form-label {
        font-weight: 600;
        color: #003b87;
    }

    /* Tabela mais larga */
    .table-responsive {
        max-width: 1250px;       /* MAIOR largura interna */
        margin: 0 auto;
    }

    .table thead th {
        font-weight: 700;
        background: #f2f4f7 !important;
    }

    /* Botão editar / excluir */
    .btn-sm {
        font-size: .8rem;
        padding: 5px 10px;
    }

    #editFormContainer {
        display: none;
    }

    /* Ajuste opcional: deixar os inputs e selects mais modernos */
    .form-control,
    .form-select {
        border-radius: 6px;
        padding: 10px;
    }

    /* Hover nas linhas da tabela */
    .table-hover tbody tr:hover {
        background-color: #f0f4fa !important;
    }

    </style>
</head>
<body>

    <?php include '../templates/gen_menu.php'; ?>

    <main class="main-content">

        <!-- Título fora da caixa -->
        <h2 class="page-title">
            <i class="bi bi-people"></i> Lista de Usuários
        </h2>

        <div class="container-fluid">
            <div class="row">

                <!-- LISTA / TABELA -->
                <div class="col-lg-8 mb-4">
                    <div class="card-modern">

                        <div class="card-header-modern">
                            <h4 class="mb-0">Usuários cadastrados</h4>
                        </div>

                        <!-- Barra de Busca -->
                        <form class="d-flex mb-3">
                            <input type="text" id="searchInput" class="form-control me-2" placeholder="Buscar por nome ou email...">
                            <button type="button" class="btn btn-primary" onclick="searchUser()">Buscar</button>
                        </form>

                        <!-- Tabela -->
                        <div class="table-responsive">
                            <table id="userTable" class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nome</th>
                                        <th>Email</th>
                                        <th>Classe</th>
                                        <th>Área</th>
                                        <th>Criado</th>
                                        <th>Modificado</th>
                                        <th style="width:120px;">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($usuarios as $us): ?>
                                        <tr>
                                            <td><?= $us["id"] ?></td>
                                            <td><?= htmlspecialchars($us["nome"]) ?></td>
                                            <td><?= htmlspecialchars($us["email"]) ?></td>
                                            <td><?= htmlspecialchars($us["classe_nome"]) ?></td>
                                            <td><?= htmlspecialchars($us["area_nome"]) ?></td>
                                            <td><?= (new DateTime($us["data_criacao"]))->format('d/m/Y H:i') ?></td>
                                            <td><?= (new DateTime($us["data_modificacao"]))->format('d/m/Y H:i') ?></td>
                                            <td>
                                                <button class="btn btn-warning btn-sm me-2"
                                                    onclick="showEditForm(
                                                        <?= $us['id'] ?>,
                                                        '<?= htmlspecialchars($us['nome'], ENT_QUOTES) ?>',
                                                        '<?= htmlspecialchars($us['email'], ENT_QUOTES) ?>',
                                                        '<?= $us['classe_id'] ?>',
                                                        '<?= $us['area_id'] ?>'
                                                    )"><i class="bi bi-pencil-square"></i></button>

                                                <button class="btn btn-danger btn-sm"
                                                    onclick="deleteUser(<?= $us['id'] ?>)"><i class="bi bi-trash-fill"></i></button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div id="message" class="mt-3"></div>

                    </div>
                </div>

                <!-- FORM DE EDIÇÃO -->
                <div class="col-lg-4 mb-4">
                    <div id="editFormContainer" class="card-modern">

                        <div class="card-header-modern">
                            <h4 class="mb-0">Editar Usuário</h4>
                        </div>

                        <form id="editForm" class="needs-validation" novalidate>
                            <input type="hidden" name="id" id="edit_id">

                            <div class="mb-3">
                                <label class="form-label">Nome</label>
                                <input type="text" name="nome" id="edit_nome" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" id="edit_email" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Senha (opcional)</label>
                                <input type="password" name="senha" id="edit_senha" class="form-control">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Classe</label>
                                <select name="classe_id" id="edit_classe" class="form-select">
                                    <?php foreach ($classes as $c): ?>
                                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nome']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Área</label>
                                <select name="area_id" id="edit_area" class="form-select">
                                    <?php foreach ($areas as $a): ?>
                                        <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['nome']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-success">
                                Salvar Alterações
                            </button>

                        </form>

                        <div id="editMessage" class="mt-3"></div>

                    </div>
                </div>

            </div>
        </div>

    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../public/js/get_us.js"></script>

</body>
</html>
