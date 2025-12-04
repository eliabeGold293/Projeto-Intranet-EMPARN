<?php
require_once "../config/connection.php";

header("Content-Type: application/json; charset=utf-8");

// Tempo máximo que o long polling espera antes de responder
$timeout = 20; // segundos
$start = time();

// Último timestamp recebido do front-end
$lastTimestamp = isset($_GET['since']) ? $_GET['since'] : 0;

while (true) {

    // Consulta logs mais recentes do que o último visto
    $stmt = $pdo->prepare("
        SELECT descricao, acao, data_acao 
        FROM log_acao
        WHERE EXTRACT(EPOCH FROM data_acao) > :last
        ORDER BY data_acao ASC
    ");

    $stmt->execute([
        ':last' => $lastTimestamp
    ]);

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($rows)) {
        echo json_encode([
            "new" => true,
            "logs" => $rows,
            "latest" => strtotime($rows[array_key_last($rows)]['data_acao'])
        ]);
        exit;
    }

    // tempo excedido → retorna vazio
    if ((time() - $start) >= $timeout) {
        echo json_encode([
            "new" => false
        ]);
        exit;
    }

    usleep(400000); // aguarda 0.4s para não sobrecarregar CPU
}
?>