<?php 
require_once "../config/connection.php";

$nome = $_POST['nome']?? null;

if ($nome){
    try{
        $sql = "INSERT INTO area_atuacao (nome) VALUES (:nome)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nome'=> $nome
            ]);

        echo "Área de Atuação adicionada com sucesso!";

    } catch (PDOException $e){
        echo "Erro ao tentar salvar nova Áread de Atuação: " . $e->getMessage();
    }
} else{
    echo "Preencha todos os campos obrigatórios.";
}

?>