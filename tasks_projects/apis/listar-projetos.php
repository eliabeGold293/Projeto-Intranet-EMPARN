<?php
header("Content-Type: application/json");
require_once __DIR__ . '/../config/connection.php';

try {

    $sql = "
        SELECT 
            p.id,
            p.nome,
            p.descricao,
            p.data_criacao,

            (SELECT COUNT(*) FROM tarefa t WHERE t.projeto_id = p.id) AS total_tarefas,

            (SELECT COUNT(*) FROM usuario_projeto up WHERE up.projeto_id = p.id) AS total_membros

        FROM projeto p
        ORDER BY p.data_criacao DESC
    ";

    $stmt = $pdo->query($sql);
    $projetos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($projetos);

} catch(Exception $e) {

    echo json_encode([]);
}