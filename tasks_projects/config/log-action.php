<?php

function logAction($pdo, $acao, $descricao = "")
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $usuario_id = $_SESSION['usuario_id'] ?? null;

    // entidade será o usuário da sessão
    $entidade = "usuario";

    $endpoint = $_SERVER['REQUEST_URI'] ?? '';
    $method   = $_SERVER['REQUEST_METHOD'] ?? '';
    $ip       = $_SERVER['REMOTE_ADDR'] ?? '';

    $descricao_final = $descricao .
        " | endpoint:$endpoint | method:$method | ip:$ip";

    $sql = "INSERT INTO log_acao
            (usuario_id, entidade, acao, descricao)
            VALUES (:usuario_id, :entidade, :acao, :descricao)";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':usuario_id' => $usuario_id,
        ':entidade'   => $entidade,
        ':acao'       => $acao,
        ':descricao'  => $descricao_final
    ]);
}