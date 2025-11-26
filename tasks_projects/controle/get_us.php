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

    <style>
        body {
            background-color: #f4f6f8;
            display: flex;
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
        }

        .main-content {
            flex: 1;
            padding: 30px;
            margin-left: 250px; /* espaço para o menu lateral */
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 20px;
            }
        }

        .card {
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }

        #editFormContainer {
            display: none; /* só aparece quando necessário */
        }
    </style>
</head>
<body>
    <!-- Menu reutilizável -->
    <?php include '../templates/gen_menu.php'; ?>

    <!-- Conteúdo principal -->
    <main class="main-content">
        <div class="container-fluid">
            <div class="row">
                <!-- Tabela -->
                <div class="col-lg-8 mb-4">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Usuários cadastrados</h5>
                        </div>
                        <div class="card-body">
                            <!-- Busca -->
                            <form class="d-flex mb-3">
                                <input type="text" id="searchInput" class="form-control me-2" placeholder="Buscar usuário por nome ou email...">
                                <button type="button" class="btn btn-primary" onclick="searchUser()">Buscar</button>
                            </form>

                            <!-- Tabela -->
                            <div class="table-responsive">
                                <table id="userTable" class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>ID</th>
                                            <th>Nome</th>
                                            <th>Email</th>
                                            <th>Classe</th>
                                            <th>Área</th>
                                            <th>Criado Em</th>
                                            <th>Modificado Em</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($usuarios as $us): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($us["id"]) ?></td>
                                                <td><?= htmlspecialchars($us["nome"]) ?></td>
                                                <td><?= htmlspecialchars($us["email"]) ?></td>
                                                <td><?= htmlspecialchars($us["classe_nome"]) ?></td>
                                                <td><?= htmlspecialchars($us["area_nome"]) ?></td>
                                                <td><?= (new DateTime($us["data_criacao"]))->format('d/m/Y H:i:s') ?></td>
                                                <td><?= (new DateTime($us["data_modificacao"]))->format('d/m/Y H:i:s') ?></td>
                                                <td>
                                                    <button class="btn btn-warning btn-sm me-2" onclick="showEditForm(
                                                        <?= $us['id'] ?>,
                                                        '<?= htmlspecialchars($us['nome'], ENT_QUOTES) ?>',
                                                        '<?= htmlspecialchars($us['email'], ENT_QUOTES) ?>',
                                                        '<?= htmlspecialchars($us['classe_id'], ENT_QUOTES) ?>',
                                                        '<?= htmlspecialchars($us['area_id'], ENT_QUOTES) ?>'
                                                    )">✏️ Editar</button>
                                                    <button class="btn btn-danger btn-sm" onclick="deleteUser(<?= $us['id'] ?>)">🗑️ Excluir</button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div id="message" class="mt-3"></div>
                        </div>
                    </div>
                </div>

                <!-- Formulário de edição -->
                <div class="col-lg-4 mb-4">
                    <div class="card" id="editFormContainer">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="mb-0">Editar Usuário</h5>
                        </div>
                        <div class="card-body">
                            <form id="editForm" class="needs-validation" novalidate>
                                <input type="hidden" name="id" id="edit_id">
                                <div class="mb-3">
                                    <label class="form-label">Nome</label>
                                    <input type="text" name="nome" id="edit_nome" class="form-control" required>
                                    <div class="invalid-feedback">Informe o nome.</div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" id="edit_email" class="form-control" required>
                                    <div class="invalid-feedback">Informe um email válido.</div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Senha (deixe em branco para não alterar)</label>
                                    <input type="password" name="senha" id="edit_senha" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Classe</label>
                                    <select name="classe_id" id="edit_classe" class="form-select">
                                        <?php foreach ($classes as $c): ?>
                                            <option value="<?= htmlspecialchars($c['id']) ?>"><?= htmlspecialchars($c['nome']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Área</label>
                                    <select name="area_id" id="edit_area" class="form-select">
                                        <?php foreach ($areas as $a): ?>
                                            <option value="<?= htmlspecialchars($a['id']) ?>"><?= htmlspecialchars($a['nome']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-success">Salvar Alterações</button>
                            </form>
                            <div id="editMessage" class="mt-3"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../public/js/get_us.js"></script>
</body>
</html>
