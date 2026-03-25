<?php

function logAction($pdo, $acao, $entidade, $entidade_id = null, $descricao = "")
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $usuario_id = $_SESSION['usuario_id'] ?? null;

    $endpoint = $_SERVER['REQUEST_URI'] ?? '';
    $method   = $_SERVER['REQUEST_METHOD'] ?? '';
    $ip       = $_SERVER['REMOTE_ADDR'] ?? '';

    if (empty($descricao)) {
        $descricao = "Ação {$acao} em {$entidade}";
    }

    $descricao_final = $descricao .
        " | endpoint:$endpoint | method:$method | ip:$ip";

    $sql = "INSERT INTO log_acao
            (usuario_id, entidade, entidade_id, acao, descricao)
            VALUES (:usuario_id, :entidade, :entidade_id, :acao, :descricao)";

    try {
        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ':usuario_id'  => $usuario_id,
            ':entidade'    => $entidade,
            ':entidade_id' => $entidade_id,
            ':acao'        => $acao,
            ':descricao'   => $descricao_final
        ]);
    } catch (Exception $e) {
        error_log("Erro ao registrar log: " . $e->getMessage());
    }
}