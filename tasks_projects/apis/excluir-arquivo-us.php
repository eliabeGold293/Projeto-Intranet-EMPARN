<?php
session_start();
require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/../utils/log-action.php'; // IMPORTANTE

try {

    if (!isset($_SESSION['usuario_id'])) {
        throw new Exception("Usuário não autenticado.");
    }

    if (!isset($_POST['id'])) {
        throw new Exception("Arquivo inválido.");
    }

    $usuario_id = $_SESSION['usuario_id'];
    $id = (int) $_POST['id'];

    // ================================
    // BUSCAR DADOS DO ARQUIVO
    // ================================
    $stmt = $pdo->prepare("SELECT * FROM arquivo_usuario WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $arquivo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$arquivo || $arquivo['usuario_id'] != $usuario_id) {
        throw new Exception("Você não tem permissão para excluir este arquivo.");
    }

    // Guarda info para log
    $nomeArquivo = $arquivo['caminho_armazenado'];

    // ================================
    // REMOVE ARQUIVO FÍSICO
    // ================================
    $caminhoFisico = __DIR__ . '/../' . $arquivo['caminho_armazenado'];

    if (file_exists($caminhoFisico)) {
        unlink($caminhoFisico);
    }

    // ================================
    // REMOVE DO BANCO
    // ================================
    $stmt = $pdo->prepare("DELETE FROM arquivo_usuario WHERE id = :id");
    $stmt->execute([':id' => $id]);

    // ================================
    // LOG
    // ================================
    try {
        registrarLog(
            $pdo,
            $usuario_id,
            'arquivo_usuario',
            'DELETE',
            "Arquivo '{$nomeArquivo}' (ID {$id}) excluído"
        );
    } catch (Exception $e) {
        error_log("Erro ao registrar log: " . $e->getMessage());
    }

    echo "<script>alert('Arquivo excluído com sucesso!'); window.location.href=document.referrer;</script>";

} catch (Exception $e) {

    echo "<script>alert('Erro: " . addslashes($e->getMessage()) . "'); window.history.back();</script>";

}