<?php
require_once "../config/connection.php";

$nome = $_POST['nome'] ?? null;

if ($nome) {
    try {
        // Verifica se já existe uma área com o mesmo nome
        $sql_check = "SELECT COUNT(*) FROM area_atuacao WHERE nome = :nome";
        $stmt_check = $pdo->prepare($sql_check);
        $stmt_check->execute([':nome' => $nome]);
        $existe = $stmt_check->fetchColumn();

        if ($existe > 0) {
            echo "Já existe uma Área de Atuação com este nome.";
        } else {
            // Se não existir, insere
            $sql = "INSERT INTO area_atuacao (nome) VALUES (:nome)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':nome' => $nome]);

            // Captura ID da nova área
            $novaAreaId = $pdo->lastInsertId();

            // Registrar ação no log
            $descricao = "📌 Área de Atuação '{$nome}' adicionada";
            $stmtLog = $pdo->prepare("INSERT INTO log_acao (usuario_id, entidade, acao, descricao) 
                                      VALUES (:usuario_id, 'area_atuacao', 'INSERIR', :descricao)");
            // Aqui você pode usar o ID do usuário logado na sessão (se tiver),
            // mas como exemplo vamos deixar NULL
            $stmtLog->execute([
                ':usuario_id' => null,
                ':descricao'  => $descricao
            ]);

            echo "Área de Atuação adicionada com sucesso!";
        }

    } catch (PDOException $e) {
        echo "Erro ao tentar salvar nova Área de Atuação: " . $e->getMessage();
    }
} else {
    echo "Preencha todos os campos obrigatórios.";
}
?>
