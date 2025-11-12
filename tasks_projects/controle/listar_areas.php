<?php
require_once "../config/connection.php"; // já contém $pdo

try {
    $stmt = $pdo->prepare("SELECT id, nome FROM area_atuacao ORDER BY nome ASC");
    $stmt->execute();
    $areas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Lista de Áreas de Atuação</title>
    <link rel="stylesheet" href="style.css"> <!-- CSS separado -->
</head>
<style>
    /* Container da tabela */
    .table-container {
        margin-left: 320px; /* espaço para a sidebar */
        margin-top: 40px;
        padding: 20px;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        max-width: 900px;
    }

    .table-container h1 {
        margin-top: 0;
        color: #333;
        font-family: Arial, sans-serif;
    }

    /* Caixa de busca */
    .search-box {
        margin-bottom: 20px;
        display: flex;
        gap: 10px;
    }

    .search-box input {
        flex: 1;
        padding: 8px;
        border: 1px solid #ccc;
        border-radius: 4px;
    }

    .search-box button {
        background: #4a90e2;
        color: #fff;
        border: none;
        padding: 8px 15px;
        border-radius: 4px;
        cursor: pointer;
        transition: background 0.3s ease;
    }

    .search-box button:hover {
        background: #357ab8;
    }

    /* Tabela moderna */
    table {
        width: 100%;
        border-collapse: collapse;
        font-family: Arial, sans-serif;
    }

    th, td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #eee;
    }

    th {
        background: #f4f6f8;
        font-weight: bold;
        color: #555;
    }

    tr:hover {
        background: #f9f9f9;
    }
</style>
<body>
    <!-- Navegação reutilizável -->
    <?php include '../templates/gen_menu.php'; ?>

    <!-- Encapsulamento do conteúdo -->
    <section class="table-container">
        <h1>Áreas de Atuação cadastradas</h1>

        <!-- Caixa de busca -->
        <div class="search-box">
            <input type="text" id="searchInput" placeholder="Buscar área por nome ou ID...">
            <button onclick="searchArea()">Buscar</button>
        </div>

        <!-- Tabela -->
        <table id="areaTable">
            <tr>
                <th>ID</th>
                <th>Nome</th>
            </tr>
            <?php foreach ($areas as $area): ?>
                <tr>
                    <td><?= htmlspecialchars($area["id"]) ?></td>
                    <td><?= htmlspecialchars($area["nome"]) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </section>

    <!-- Script simples para busca -->
    <script>
        function searchArea() {
            const input = document.getElementById("searchInput").value.toLowerCase();
            const rows = document.querySelectorAll("#areaTable tr:not(:first-child)");

            rows.forEach(row => {
                const id = row.cells[0].textContent.toLowerCase();
                const nome = row.cells[1].textContent.toLowerCase();
                if (id.includes(input) || nome.includes(input)) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            });
        }
    </script>
</body>
</html>
