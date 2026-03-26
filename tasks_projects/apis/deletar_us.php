<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/../utils/log-action.php';

$id = isset($_POST["id"]) ? (int) $_POST["id"] : 0;

if ($id <= 0) {
    echo "Informe um ID válido.";
    exit;
}

try {

    // ================================
    // BUSCAR DADOS ANTES DE EXCLUIR
    // ================================
    $stmtNome = $pdo->prepare("SELECT nome FROM usuario WHERE id = :id");
    $stmtNome->execute([":id" => $id]);
    $usuario = $stmtNome->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        echo "Usuário não encontrado.";
        exit;
    }

    // ================================
    // DELETE
    // ================================
    $stmt = $pdo->prepare("DELETE FROM usuario WHERE id = :id");
    $stmt->execute([":id" => $id]);

    // ================================
    // LOG DE AÇÃO 
    // ================================
    try {

        $usuarioLogadoId = $_SESSION['usuario_id'] ?? null;

        registrarLog(
            $pdo,
            $usuarioLogadoId,
            'usuario',
            'DELETE',
            "Excluiu usuário ID {$id} ({$usuario['nome']})"
        );

    } catch (Exception $e) {
        error_log("Erro ao registrar log: " . $e->getMessage());
    }

    echo "Usuário deletado com sucesso!";

} catch (PDOException $e) {
    echo "Erro ao tentar deletar: " . $e->getMessage();
}