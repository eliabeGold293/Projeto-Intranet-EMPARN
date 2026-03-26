<?php
require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/../utils/log-action.php';

session_start();

$nome = $_POST['nome'] ?? null;

// Usuário logado (se existir)
$usuarioLogadoId = $_SESSION['usuario_id'] ?? null;

if ($nome) {
    try {
        // Verifica se já existe
        $sql_check = "SELECT COUNT(*) FROM area_atuacao WHERE UPPER(nome) = UPPER(:nome)";
        $stmt_check = $pdo->prepare($sql_check);
        $stmt_check->execute([':nome' => $nome]);
        $existe = $stmt_check->fetchColumn();

        if ($existe > 0) {
            echo "Já existe uma Área de Atuação com este nome.";
            exit;
        }

        // Inserir nova área
        $sql = "INSERT INTO area_atuacao (nome) VALUES (:nome)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':nome' => $nome]);

        // Captura ID
        $novaAreaId = $pdo->lastInsertId();

        // ============================
        // LOG PADRONIZADO
        // ============================
        $descricao = "Área de Atuação '{$nome}' criada";

        registrarLog(
            $pdo,
            $usuarioLogadoId,
            "area_atuacao",
            "CREATE",
            $descricao
        );

        echo "Área de Atuação adicionada com sucesso!";

    } catch (Exception $e) {
        error_log("Erro ao criar área: " . $e->getMessage());
        echo "Erro ao tentar salvar nova Área de Atuação.";
    }

} else {
    echo "Preencha todos os campos obrigatórios.";
}
?>