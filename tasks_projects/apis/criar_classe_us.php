<?php 
require_once "../config/connection.php";

$nome = $_POST['nome']?? null;
$grau_acesso = isset($_POST['grau_acesso']) ? (int) $_POST['grau_acesso'] : 0;

if ($nome && $grau_acesso){
    try{
        $sql = "INSERT INTO classe_usuario (nome, grau_acesso) VALUES (:nome, :grau_acesso)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nome'=> $nome,
                ':grau_acesso'=>$grau_acesso
            ]);

        echo "Classe adicionada com sucesso!";

    } catch (PDOException $e){
        echo "Erro ao tentar salvar nova classe: " . $e->getMessage();
    }
} else{
    echo "Preencha todos os campos obrigatórios.";
}

?>