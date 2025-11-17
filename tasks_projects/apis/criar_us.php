<?php

require_once "../config/connection.php";

$nome = $_POST['nome']?? null;
$email = $_POST['email']?? null;
$senha = $_POST['senha']?? null;
$classe_id = isset($_POST['classe_id']) ? (int) $_POST['classe_id'] : null;
$area_id   = isset($_POST['area_id']) ? (int) $_POST['area_id'] : null;

if ($nome && $email && $senha && $classe_id && $area_id){
    try{
        $sql = "INSERT INTO usuario (nome, email, senha, classe_id, area_id) VALUES (:nome, :email, :senha, :classe_id, :area_id)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nome'     => $nome,
                ':email'    => $email,
                ':senha' => $senha,
                ':classe_id' => $classe_id,
                ':area_id' => $area_id
            ]);

        echo "Cadastro realizado com sucesso!";

    } catch (PDOException $e){
        echo "Erro ao salvar: " . $e->getMessage();
    }
} else{
    echo "Preencha todos os campos obrigatórios.";
}

?>