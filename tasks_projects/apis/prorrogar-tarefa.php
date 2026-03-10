<?php

session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login");
    exit;
}

require_once __DIR__ . '/../config/connection.php';

try {

    // Detecta se veio JSON
    $data = json_decode(file_get_contents("php://input"), true);

    if ($data) {
        $tarefa_id = $data['tarefa_id'] ?? null;
        $novo_prazo = $data['novo_prazo'] ?? null;
    } else {
        $tarefa_id = $_POST['tarefa_id'] ?? null;
        $novo_prazo = $_POST['novo_prazo'] ?? null;
    }

    if (!$tarefa_id || !$novo_prazo) {
        throw new Exception("Dados da tarefa inválidos.");
    }

    $sql = "
        UPDATE tarefa
        SET prazo = :prazo
        WHERE id = :id
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->bindParam(':prazo', $novo_prazo);
    $stmt->bindParam(':id', $tarefa_id, PDO::PARAM_INT);

    if (!$stmt->execute()) {
        throw new Exception("Erro ao executar atualização.");
    }

    $_SESSION['alerta'] = [
        'tipo' => 'success',
        'mensagem' => 'Prazo da tarefa atualizado com sucesso!'
    ];

} catch (Exception $e) {

    $_SESSION['alerta'] = [
        'tipo' => 'danger',
        'mensagem' => 'Erro ao atualizar prazo: ' . $e->getMessage()
    ];
}

$redirect = $_SERVER['HTTP_REFERER'] ?? 'home';

header("Location: " . $redirect);
exit;