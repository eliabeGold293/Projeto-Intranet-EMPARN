<?php

session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login");
    exit;
}

require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/../utils/log-action.php';

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

    // ======================================
    // BUSCAR DADOS ANTES
    // ======================================
    $stmtAntes = $pdo->prepare("
        SELECT t.titulo, t.prazo, p.titulo AS projeto
        FROM tarefa t
        JOIN projeto p ON p.id = t.projeto_id
        WHERE t.id = :id
    ");
    $stmtAntes->execute([':id' => $tarefa_id]);

    $antes = $stmtAntes->fetch(PDO::FETCH_ASSOC);

    if (!$antes) {
        throw new Exception("Tarefa não encontrada.");
    }

    $prazoAntigo = $antes['prazo'] ?? 'sem prazo';

    // ======================================
    // UPDATE
    // ======================================
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

    // ======================================
    // BUSCAR USUÁRIO LOGADO
    // ======================================
    $stmtUser = $pdo->prepare("SELECT nome FROM usuario WHERE id = :id");
    $stmtUser->execute([':id' => $_SESSION['usuario_id']]);

    $usuarioLog = $stmtUser->fetchColumn() ?? "Usuário desconhecido";

    // ======================================
    // LOG
    // ======================================
    try {
        registrarLog(
            $pdo,
            $_SESSION['usuario_id'],
            'tarefa',
            'UPDATE',
            "Prazo da tarefa '{$antes['titulo']}' (projeto '{$antes['projeto']}') alterado de '{$prazoAntigo}' para '{$novo_prazo}' por '{$usuarioLog}'"
        );
    } catch (Exception $e) {
        error_log("Erro ao registrar log: " . $e->getMessage());
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
