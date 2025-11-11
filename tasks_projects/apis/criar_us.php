<?php

require_once "../config/connection.php";

$nome = $_POST['nome']?? null;
$email = $_POST['email']?? null;
$senha = $_POST['senha']?? null;
$classe_name = $_POST['classe_name']?? null;
$area_name = $_POST['area_name']?? null;

if ($nome && $email && $senha && $classe_name && $area_name){
    try{
        $sql = "INSERT INTO usuario (nome, email, senha, classe_name, area_name) VALUES (:nome, :email, :senha, :classe_name, :area_name)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nome'     => $nome,
                ':email'    => $email,
                ':senha' => $senha,
                ':classe_name' => $classe_name,
                ':area_name' => $area_name
            ]);

        echo "Cadastro realizado com sucesso!";

    } catch (PDOException $e){
        echo "Erro ao salvar: " . $e->getMessage();
    }
} else{
    echo "Preencha todos os campos obrigatórios.";
}

?>