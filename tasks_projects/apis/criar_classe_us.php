<?php 
declare(strict_types=1);

session_start();

require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/../utils/log-action.php';

$nome = $_POST['nome'] ?? null;
$grau_acesso = isset($_POST['grau_acesso']) ? (int) $_POST['grau_acesso'] : 0;

if (!$nome || !$grau_acesso) {
    echo "Preencha todos os campos obrigatórios.";
    exit;
}

try {

    // ================================
    // VERIFICAR DUPLICIDADE
    // ================================
    $check = $pdo->prepare("SELECT COUNT(*) FROM classe_usuario WHERE UPPER(nome) = UPPER(:nome)");
    $check->execute([":nome" => $nome]);
    $existe = $check->fetchColumn();

    if ($existe > 0) {
        echo "Já existe uma classe com o nome \"$nome\".";
        exit;
    }

    // ================================
    // INSERT
    // ================================
    $stmt = $pdo->prepare("
        INSERT INTO classe_usuario (nome, grau_acesso) 
        VALUES (:nome, :grau_acesso)
    ");

    $stmt->execute([
        ':nome' => $nome,
        ':grau_acesso' => $grau_acesso
    ]);

    $novaClasseId = $pdo->lastInsertId();

    // ================================
    // LOG DE AÇÃO 
    // ================================
    try {

        $usuarioLogadoId = $_SESSION['usuario_id'] ?? null;

        registrarLog(
            $pdo,
            $usuarioLogadoId,
            'classe_usuario',
            'CREATE',
            "Criou classe ID {$novaClasseId} ({$nome}) com grau {$grau_acesso}"
        );

    } catch (Exception $e) {
        error_log("Erro ao registrar log: " . $e->getMessage());
    }

    echo "Classe adicionada com sucesso!";

} catch (PDOException $e) {
    echo "Erro ao tentar salvar nova classe: " . $e->getMessage();
}