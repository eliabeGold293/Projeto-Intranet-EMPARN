<?php 
# API para atualizar informações do usuário

require_once "../config/connection.php";

$id          = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$nome        = $_POST['nome'] ?? null;
$email       = $_POST['email'] ?? null;
$senha       = $_POST['senha'] ?? null;
$classe_name = $_POST['classe_name'] ?? null;
$area_name   = $_POST['area_name'] ?? null;

if ($id > 0) {
    try {
        // Monta dinamicamente os campos que serão atualizados
        $campos = [];
        $params = [':id' => $id];

        if ($nome) {
            $campos[] = "nome = :nome";
            $params[':nome'] = $nome;
        }
        if ($email) {
            $campos[] = "email = :email";
            $params[':email'] = $email;
        }
        if ($senha) {
            // Criptografa a senha antes de salvar
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            $campos[] = "senha = :senha";
            $params[':senha'] = $senha_hash;
        }
        if ($classe_name) {
            $campos[] = "classe_name = :classe_name";
            $params[':classe_name'] = $classe_name;
        }
        if ($area_name) {
            $campos[] = "area_name = :area_name";
            $params[':area_name'] = $area_name;
        }

        if (!empty($campos)) {
            $sql = "UPDATE usuario SET " . implode(", ", $campos) . " WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            echo "✅ Alterações realizadas com sucesso!";
        } else {
            echo "⚠️ Nenhum campo foi informado para atualização.";
        }

    } catch (PDOException $e) {
        echo "❌ Erro ao salvar alterações: " . $e->getMessage();
    }
} else {
    echo "⚠️ Informe um ID válido.";
}


