<?php
require_once "../config/connection.php";

$nome = $_POST['nome'] ?? null;
$email = $_POST['email'] ?? null;
$senha = $_POST['senha'] ?? null;
$classe_id = isset($_POST['classe_id']) ? (int) $_POST['classe_id'] : null;
$area_id   = isset($_POST['area_id']) ? (int) $_POST['area_id'] : null;

if ($nome && $email && $senha && $classe_id && $area_id) {
    try {
        // Inserir usuário
        $sql = "INSERT INTO usuario (nome, email, senha, classe_id, area_id) 
                VALUES (:nome, :email, :senha, :classe_id, :area_id)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nome'      => $nome,
            ':email'     => $email,
            ':senha'     => $senha, // ideal usar password_hash()
            ':classe_id' => $classe_id,
            ':area_id'   => $area_id
        ]);

        // Captura ID do novo usuário
        $novoUsuarioId = $pdo->lastInsertId();

        // Registrar ação no log
        $descricao = "👤 Usuário '{$nome}' cadastrado";
        $stmtLog = $pdo->prepare("INSERT INTO log_acao (usuario_id, entidade, acao, descricao) 
                                  VALUES (:usuario_id, 'usuario', 'INSERIR', :descricao)");
        // Aqui você pode usar o ID do usuário logado na sessão, se houver.
        $stmtLog->execute([
            ':usuario_id' => $novoUsuarioId,
            ':descricao'  => $descricao
        ]);

        echo "Cadastro realizado com sucesso!";

    } catch (PDOException $e) {
        echo "Erro ao salvar: " . $e->getMessage();
    }
} else {
    echo "Preencha todos os campos obrigatórios.";
}
?>
