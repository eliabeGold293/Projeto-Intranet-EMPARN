<?php
function registrarLog($pdo, $usuarioId, $entidade, $acao, $descricao = null) {
    try {
        $sql = "INSERT INTO log_acao (usuario_id, entidade, acao, descricao)
                VALUES (:usuario_id, :entidade, :acao, :descricao)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':usuario_id' => $usuarioId,
            ':entidade' => $entidade,
            ':acao' => $acao,
            ':descricao' => $descricao
        ]);

    } catch (Exception $e) {
        // opcional: salvar erro em arquivo
        error_log("Erro ao registrar log: " . $e->getMessage());
    }
}